<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookFile;
use App\Models\User;

class BookWorkflowActionService
{
    public function executePrimary(Book $book, ?User $user, array $payload = []): array
    {
        $guard = app(BookWorkflowGuardService::class)->evaluate($book, $user);

        if (!empty($guard['primaryDisabled'])) {
            $reason = $guard['blockers'][0] ?? 'Aksi utama tidak dapat dijalankan saat ini.';

            return [
                'status' => 'warning',
                'message' => $reason,
            ];
        }

        $mode = (string) ($guard['primaryMode'] ?? 'next');

        return match ($mode) {
            'done' => [
                'status' => 'info',
                'message' => 'Workflow sudah selesai. Tidak ada aksi utama lanjutan.',
            ],
            'audit' => $this->runAudit($book),
            'submit' => $this->submitIsbn($book),
            'approve_isbn' => $this->approveIsbn($book, $payload),
            'author_approval' => $this->authorApproval($book, $user),
            default => $this->nextWorkflow($book),
        };
    }

    public function prepareIsbn(Book $book, ?User $user): array
    {
        $guard = app(BookWorkflowGuardService::class)->evaluate($book, $user);

        if (!empty($guard['isFinished'])) {
            return [
                'status' => 'info',
                'message' => 'Workflow sudah selesai. Prepare ISBN tidak diperlukan.',
            ];
        }

        $generator = app(DocumentGeneratorService::class);

        $titlePage = $generator->generateTitlePage($book);
        $this->upsertGeneratedFile($book, 'halaman_judul', $titlePage);

        $copyright = $generator->generateCopyright($book);
        $this->upsertGeneratedFile($book, 'copyright', $copyright);

        $requestLetter = $generator->generateRequestLetter($book);
        $this->upsertGeneratedFile($book, 'surat_permohonan', $requestLetter);

        $attachment = $generator->generateAttachment($book);
        $this->upsertGeneratedFile($book, 'attachment_isbn', $attachment);

        app(BookActivityService::class)->log(
            $book,
            'Prepare ISBN',
            'Dokumen ISBN otomatis dibuat dari Action Center.'
        );

        $auditResult = $this->runAudit($book);

        if (($auditResult['status'] ?? 'success') === 'warning') {
            return [
                'status' => 'warning',
                'message' => 'Dokumen berhasil dipersiapkan, namun masih ada catatan audit: ' . ($auditResult['message'] ?? ''),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Prepare ISBN selesai: dokumen diperbarui dan audit dijalankan.',
        ];
    }

    private function nextWorkflow(Book $book): array
    {
        $book->load('publishingPackage');

        if (!$book->hasPaidInitialPackageInvoice()) {
            return [
                'status' => 'warning',
                'message' => $book->dpPaymentWarningMessage(),
            ];
        }

        $workflows = $book->workflowSteps();
        $current = array_search($book->workflow_status, $workflows, true);

        if ($current === false || !isset($workflows[$current + 1])) {
            return [
                'status' => 'info',
                'message' => 'Tidak ada tahap lanjutan yang tersedia.',
            ];
        }

        $nextWorkflow = $workflows[$current + 1];
        $data = [
            'workflow_status' => $nextWorkflow,
        ];

        if ($nextWorkflow === 'editing' && !$book->tanggal_mulai_editing) {
            $data['tanggal_mulai_editing'] = now();
        }

        if ($nextWorkflow === 'layout' && !$book->tanggal_mulai_layout) {
            $data['tanggal_mulai_layout'] = now();
        }

        if ($nextWorkflow === 'cover_design' && !$book->tanggal_mulai_cover) {
            $data['tanggal_mulai_cover'] = now();
        }

        if ($nextWorkflow === 'acc_penulis' && !$book->tanggal_acc_penulis) {
            $data['tanggal_acc_penulis'] = now();
        }

        $book->update($data);

        if ($nextWorkflow === 'selesai') {
            app(BookCompletionOrchestratorService::class)->handle($book, 'workflow_next');
        }

        app(BookActivityService::class)->log(
            $book,
            'Workflow Berubah',
            $nextWorkflow
        );

        return [
            'status' => 'success',
            'message' => 'Workflow berhasil dilanjutkan ke tahap ' . strtoupper(str_replace('_', ' ', $nextWorkflow)) . '.',
        ];
    }

    private function runAudit(Book $book): array
    {
        app(IsbnAuditService::class)->run($book);
        app(WorkflowService::class)->update($book, app(BookActivityService::class));

        app(BookActivityService::class)->log(
            $book,
            'Audit ISBN',
            'Audit ISBN dijalankan dari Action Center.'
        );

        $missing = $book->missingIsbnAuditFiles();

        if (!empty($missing)) {
            return [
                'status' => 'warning',
                'message' => 'Audit dijalankan, namun dokumen wajib belum lengkap: ' . implode(', ', array_map(fn(string $type) => $this->fileTypeLabel($type), $missing)),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Audit ISBN selesai.',
        ];
    }

    private function submitIsbn(Book $book): array
    {
        if (!$book->canSubmitIsbnToPerpusnas()) {
            return [
                'status' => 'warning',
                'message' => 'Submit ISBN hanya bisa dilakukan saat status buku berada di READY FOR ISBN.',
            ];
        }

        $book->update([
            'workflow_status' => 'isbn_submitted',
            'tanggal_pengajuan_isbn' => now(),
        ]);

        app(BookActivityService::class)->log(
            $book,
            'Submit ISBN',
            'Naskah diajukan ke Perpusnas dari Action Center.'
        );

        return [
            'status' => 'success',
            'message' => 'Buku berhasil diajukan ke ISBN.',
        ];
    }

    private function approveIsbn(Book $book, array $payload): array
    {
        if (!$book->canApproveIsbnIssued()) {
            return [
                'status' => 'warning',
                'message' => 'Terbitkan ISBN hanya bisa dilakukan saat status buku berada di ISBN SUBMITTED.',
            ];
        }

        $isbn = trim((string) ($payload['isbn'] ?? ''));
        $tanggal = trim((string) ($payload['tanggal'] ?? ''));

        if ($isbn === '' || $tanggal === '') {
            return [
                'status' => 'warning',
                'message' => 'Nomor ISBN dan tanggal terbit wajib diisi.',
            ];
        }

        if (!$this->isValidDateYmd($tanggal)) {
            return [
                'status' => 'warning',
                'message' => 'Format tanggal terbit tidak valid. Gunakan format YYYY-MM-DD.',
            ];
        }

        $verification = app(PerpusnasIsbnService::class)->verify(
            $isbn,
            (string) $book->judul,
            $book->tahun_terbit ? (string) $book->tahun_terbit : null
        );

        if (!$verification['verified']) {
            return [
                'status' => 'warning',
                'message' => 'Validasi ISBN API gagal: ' . ($verification['message'] ?? 'ISBN tidak ditemukan.'),
            ];
        }

        $book->update([
            'isbn' => $isbn,
            'tanggal_isbn_terbit' => $tanggal,
            'workflow_status' => 'isbn_approved',
        ]);

        app(BookActivityService::class)->log(
            $book,
            'ISBN API Verified',
            (string) ($verification['message'] ?? 'ISBN terverifikasi di API Perpusnas')
        );

        $book->update([
            'workflow_status' => 'selesai',
        ]);

        app(BookCompletionOrchestratorService::class)->handle($book, 'isbn_api_verified');

        app(BookActivityService::class)->log(
            $book,
            'Produksi Selesai Otomatis',
            'Workflow diubah ke selesai setelah ISBN tervalidasi API Perpusnas.'
        );

        return [
            'status' => 'success',
            'message' => 'ISBN berhasil diverifikasi API dan buku otomatis ditandai selesai.',
        ];
    }

    private function authorApproval(Book $book, ?User $user): array
    {
        if (!$book->hasPaidInitialPackageInvoice()) {
            return [
                'status' => 'warning',
                'message' => $book->dpPaymentWarningMessage(),
            ];
        }

        if (!$user || $user->role !== 'author' || (int) ($book->author_user_id ?? 0) !== (int) $user->id) {
            return [
                'status' => 'warning',
                'message' => 'ACC Penulis hanya dapat dilakukan oleh author pemilik naskah.',
            ];
        }

        $book->update([
            'workflow_status' => 'audit_isbn',
            'tanggal_acc_penulis' => now(),
        ]);

        app(BookActivityService::class)->log(
            $book,
            'ACC Penulis',
            'Naskah disetujui penulis dari Action Center.'
        );

        return [
            'status' => 'success',
            'message' => 'ACC Penulis berhasil.',
        ];
    }

    private function upsertGeneratedFile(Book $book, string $type, array $result): void
    {
        $book->files()
            ->where('type', $type)
            ->update(['is_active' => false]);

        BookFile::create([
            'book_id' => $book->id,
            'type' => $type,
            'original_name' => $result['file_name'],
            'file_path' => $result['file_path'],
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_size' => filesize(storage_path('app/' . $result['file_path'])),
            'is_active' => true,
        ]);
    }

    private function fileTypeLabel(string $type): string
    {
        return match ($type) {
            'cover' => 'Cover',
            'skk' => 'SKK',
            'halaman_judul' => 'Halaman Judul',
            'surat_permohonan' => 'Surat Permohonan',
            'copyright' => 'Copyright',
            'naskah_final' => 'Naskah Final',
            'attachment_isbn' => 'Attachment ISBN',
            default => strtoupper(str_replace('_', ' ', $type)),
        };
    }

    private function isValidDateYmd(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
