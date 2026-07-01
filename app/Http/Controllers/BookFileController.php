<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\Request;
use App\Services\BookActivityService;
use App\Services\FinalBookPackageService;

class BookFileController extends Controller
{
    public function store(
        Request $request,
        Book $book,
        \App\Services\NotificationService $notification,
        FinalBookPackageService $finalPackage
    ) {
        $request->validate([
            'type' => 'required|string',
            'file' => 'required|file|max:20480',
            'note' => 'nullable|string|max:1000',
        ]);

        $reviewTypeToStatus = [
            'edited_manuscript' => 'editing_review',
            'layout_pdf' => 'layout_review',
            'cover_final' => 'cover_review',
        ];

        if (isset($reviewTypeToStatus[$request->type])) {
            $targetStatus = $reviewTypeToStatus[$request->type];
            $targetIndex = array_search($targetStatus, Book::WORKFLOWS, true);
            $currentIndex = $book->workflowIndex();

            if ($targetIndex !== false && $currentIndex !== false && $currentIndex > $targetIndex) {
                return back()->with(
                    'warning',
                    'Tahap review untuk berkas ini sudah lewat. Upload dibatalkan agar pipeline tidak kembali ke tahap sebelumnya.'
                )->withInput();
            }
        }

        if (in_array($request->type, $finalPackage->requiredTypes(), true)) {
            if (!in_array(auth()->user()->role, ['admin', 'isbn', 'superadmin'], true)) {
                abort(403, 'Hanya admin/isbn/superadmin yang dapat upload berkas final author.');
            }

            try {
                $finalPackage->validateAndStore(
                    $book,
                    (string) $request->type,
                    $request->file('file'),
                    $request->note,
                    (string) auth()->user()->role
                );
            } catch (\Throwable $e) {
                return back()->with('danger', $e->getMessage())->withInput();
            }

            app(BookActivityService::class)->log(
                $book,
                'Upload Berkas Final',
                auth()->user()->name . ' mengupload berkas final ' . $request->type
            );

            return back()->with('success', 'Berkas final berhasil diupload.');
        }

        $book->files()
            ->where(
                'type',
                $request->type
            )
            ->update([

                'is_active' => false

            ]);

        if ($request->type === 'naskah_final') {

            $request->validate([
                'file' => 'required|mimes:docx'
            ]);
        }

        if ($request->type === 'cover') {

            $request->validate([
                'file' => 'required|mimes:jpg,jpeg,png,pdf'
            ]);
        }

        if ($request->type === 'skk') {

            $request->validate([
                'file' => 'required|mimes:doc,docx,jpg,jpeg,png,pdf'
            ]);
        }

        if (in_array($request->type, ['hki', 'sertifikat_penulis'], true)) {
            $request->validate([
                'file' => 'required|mimes:doc,docx,jpg,jpeg,png,pdf'
            ]);
        }

        if (in_array($request->type, ['edited_manuscript', 'halaman_judul', 'surat_permohonan', 'copyright'], true)) {
            $request->validate([
                'file' => 'required|mimes:doc,docx,pdf'
            ]);
        }

        if ($request->type === 'layout_pdf') {
            $request->validate([
                'file' => 'required|mimes:pdf'
            ]);
        }

        if ($request->type === 'cover_final') {
            $request->validate([
                'file' => 'required|mimes:jpg,jpeg,png,pdf'
            ]);
        }

        $file = $request->file('file');

        $path = $file->store(
            'books/' . $book->nomor_naskah,
            'public'
        );

        $latestVersion = BookFile::where(
            'book_id',
            $book->id
        )
            ->where(
                'type',
                $request->type
            )
            ->max('version');

        $version =
            $latestVersion
            ? $latestVersion + 1
            : 1;

        BookFile::create([

            'book_id' => $book->id,

            'type' => $request->type,

            'original_name' =>
                $file->getClientOriginalName(),

            'note' =>
                $request->note,

            'sender_role' =>
                auth()->user()->role,

            'file_path' => $path,

            'mime_type' =>
                $file->getMimeType(),

            'file_size' =>
                $file->getSize(),

            'is_active' => true,

            'version' =>
                $version,

        ]);

        $authorId = $book->author_user_id;

        if (
            $request->type === 'edited_manuscript'
        ) {

            $moved = $this->moveWorkflowForward(
                $book,
                'editing_review'
            );

            if ($moved && $authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Hasil editing buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        if (
            $request->type === 'layout_pdf'
        ) {

            $moved = $this->moveWorkflowForward(
                $book,
                'layout_review'
            );

            if ($moved && $authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Hasil layout buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        if (
            $request->type === 'cover_final'
        ) {

            $moved = $this->moveWorkflowForward(
                $book,
                'cover_review'
            );

            if ($moved && $authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Cover buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        app(
            BookActivityService::class
        )->log(

                $book,

                'Upload File',

                auth()->user()->name .
                ' mengupload ' .
                $request->type

            );

        return back()->with(
            'success',
            'File berhasil diupload'
        );
    }
    public function restore(
        BookFile $file
    ) {
        BookFile::where(
            'book_id',
            $file->book_id
        )
            ->where(
                'type',
                $file->type
            )
            ->update([
                'is_active' => false
            ]);

        $file->update([
            'is_active' => true
        ]);

        return back()
            ->with(
                'success',
                'Versi berhasil dipulihkan'
            );
    }

    public function submitISBN(
        Book $book
    ) {
        $book->update([

            'workflow_status' =>
                'isbn_submitted',

            'tanggal_pengajuan_isbn' =>
                now()

        ]);

        return back()->with(

            'success',

            'Buku berhasil diajukan ke ISBN'

        );
    }

    private function moveWorkflowForward(
        Book $book,
        string $targetStatus
    ): bool {
        $targetIndex = array_search(
            $targetStatus,
            Book::WORKFLOWS,
            true
        );

        if ($targetIndex === false) {
            return false;
        }

        $currentIndex = array_search(
            (string) $book->workflow_status,
            Book::WORKFLOWS,
            true
        );

        if ($currentIndex !== false && $currentIndex >= $targetIndex) {
            return false;
        }

        $book->update([
            'workflow_status' => $targetStatus,
        ]);

        return true;
    }
}