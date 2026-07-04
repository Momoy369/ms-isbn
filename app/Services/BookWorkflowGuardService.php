<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;

class BookWorkflowGuardService
{
    public function evaluate(Book $book, ?User $user): array
    {
        $canIsbnControl = $user
            ? in_array($user->role, ['admin', 'isbn', 'superadmin'], true)
            : false;

        $isFinished = $book->workflow_status === 'selesai';
        $isAuthorOwner = $user
            ? $user->role === 'author' && (int) ($book->author_user_id ?? 0) === (int) $user->id
            : false;

        $missingAuditFiles = $book->missingIsbnAuditFiles();
        $missingPackageFiles = $book->missingIsbnPackageFiles();

        $dpPaid = $book->hasPaidInitialPackageInvoice();
        $dpWarning = $book->dpPaymentWarningMessage();

        $canSubmitIsbn = $book->canSubmitIsbnToPerpusnas() && $canIsbnControl;
        $canApproveIsbn = $book->canApproveIsbnIssued() && $canIsbnControl;
        $canDownloadPackage = $book->canGenerateIsbnPackage() && empty($missingPackageFiles);

        $blockers = [];

        if (!$dpPaid) {
            $blockers[] = $dpWarning;
        }

        if ($book->workflow_status === 'ready_for_isbn' && !$canIsbnControl) {
            $blockers[] = 'Submit Perpusnas hanya dapat dilakukan role Admin/ISBN/Superadmin.';
        }

        if ($book->workflow_status === 'isbn_submitted' && !$canIsbnControl) {
            $blockers[] = 'Validasi ISBN terbit hanya dapat dilakukan role Admin/ISBN/Superadmin.';
        }

        if ($book->workflow_status === 'acc_penulis' && !$isAuthorOwner) {
            $blockers[] = 'Menunggu persetujuan dari author pemilik naskah.';
        }

        $primaryMode = 'next';

        if ($isFinished) {
            $primaryMode = 'done';
        } elseif ($book->workflow_status === 'audit_isbn') {
            $primaryMode = 'audit';
        } elseif ($book->workflow_status === 'ready_for_isbn') {
            $primaryMode = 'submit';
        } elseif ($book->workflow_status === 'isbn_submitted') {
            $primaryMode = 'approve_isbn';
        } elseif ($book->workflow_status === 'acc_penulis') {
            $primaryMode = 'author_approval';
        }

        $approvals = $this->buildApprovalSummary($book);

        // All books use parallel workflow
        $parallelService = app(\App\Services\ParallelWorkflowService::class);

        // Check if admin can do ACC penulis (when author not registered)
        $adminCanAccPenulis = $user ? $parallelService->canAccPenulisByAdmin($book, $user) : false;

        // If admin can do ACC penulis, add it to available steps
        $availableSteps = $parallelService->getAvailableNextSteps($book, $user);
        if ($adminCanAccPenulis && !in_array('acc_penulis', $availableSteps, true)) {
            $availableSteps[] = 'acc_penulis';
        }

        return [
            'statusLabel' => strtoupper(str_replace('_', ' ', (string) $book->workflow_status)),
            'progressPercent' => $book->progressPercent(),

            'canIsbnControl' => $canIsbnControl,
            'isFinished' => $isFinished,
            'isAuthorOwner' => $isAuthorOwner,

            'missingAuditFiles' => $missingAuditFiles,
            'missingPackageFiles' => $missingPackageFiles,
            'missingAuditFileLabels' => array_map(fn(string $type) => $this->fileTypeLabel($type), $missingAuditFiles),
            'missingPackageFileLabels' => array_map(fn(string $type) => $this->fileTypeLabel($type), $missingPackageFiles),

            'canSubmitIsbn' => $canSubmitIsbn,
            'canApproveIsbn' => $canApproveIsbn,
            'canDownloadPackage' => $canDownloadPackage,

            'dpPaid' => $dpPaid,
            'dpWarning' => $dpWarning,

            'primaryMode' => $primaryMode,
            'primaryDisabled' => !empty($blockers),

            'blockers' => $blockers,

            'approvals' => $approvals['items'],
            'approvedCount' => $approvals['approvedCount'],
            'totalApprovals' => $approvals['total'],
            'allApprovalsComplete' => $approvals['approvedCount'] === $approvals['total'],

            'canViewRoyaltyCuration' => $user
                ? in_array($user->role, ['admin', 'owner', 'finance', 'superadmin'], true)
                : false,

            // Parallel workflow specific data
            'isParallelWorkflow' => true,
            'availableNextSteps' => $availableSteps,
            'trackStatus' => $parallelService->getTrackStatus($book),
            'pipelineStepStatus' => $parallelService->getPipelineStepStatus($book),
            'adminCanAccPenulis' => $adminCanAccPenulis,
            'authorRegistered' => $parallelService->isAuthorRegistered($book),
        ];
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

    private function buildApprovalSummary(Book $book): array
    {
        $hasEditing = app(\App\Services\ParallelWorkflowService::class)->hasEditing($book);

        $types = $hasEditing
            ? ['editor', 'layout', 'author']
            : ['layout', 'author'];
        $items = [];
        $approvedCount = 0;

        foreach ($types as $type) {
            $approval = $book->approvals->where('approval_type', $type)->first();
            $approved = !empty($approval);

            if ($approved) {
                $approvedCount++;
            }

            $items[] = [
                'type' => $type,
                'approved' => $approved,
                'approvedBy' => $approval->approved_by ?? null,
            ];
        }

        return [
            'items' => $items,
            'approvedCount' => $approvedCount,
            'total' => count($types),
        ];
    }
}
