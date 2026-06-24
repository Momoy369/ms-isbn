<?php

namespace App\Services;

use App\Models\Book;
use App\Services\BookActivityService;

class WorkflowService
{
    public function update(Book $book, BookActivityService $activity)
    {
        $required = [

            'cover',
            'skk',
            'halaman_judul',
            'surat_permohonan',
            'copyright'

        ];

        foreach ($required as $type) {

            if (
                !$book->files()
                    ->where('type', $type)
                    ->exists()
            ) {

                $book->update([
                    'workflow_status' => 'draft'
                ]);

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

            $activity->log(

                $book,

                'Audit ISBN',

                'Semua audit berhasil'

            );

        }
    }
}