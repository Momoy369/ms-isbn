<?php

namespace App\Services;

use App\Models\Book;
use App\Services\BookActivityService;

class WorkflowService
{
    public function update(Book $book, ?BookActivityService $activity = null)
    {
        $required = Book::ISBN_AUDIT_REQUIRED_FILES;

        foreach ($required as $type) {

            if (
                !$book->files()
                    ->where('type', $type)
                    ->exists()
            ) {

                if ($activity) {
                    $activity->log(

                        $book,

                        'Audit ISBN',

                        'Dokumen wajib belum lengkap, status pipeline dipertahankan.'

                    );
                }

                return;
            }
        }

        $failedAudit =
            $book->audits()
                ->where('passed', false)
                ->exists();

        if ($failedAudit) {

            $book->update([
                'workflow_status' =>
                    'revisi'
            ]);

            return;
        }

        if (
            $book->workflow_status
            ===
            'audit_isbn'
        ) {

            $book->update([

                'workflow_status' =>
                    'ready_for_isbn'

            ]);

            if ($activity) {
                $activity->log(

                    $book,

                    'Audit ISBN',

                    'Semua audit berhasil'

                );
            }

        }
    }
}