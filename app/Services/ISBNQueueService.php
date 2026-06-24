<?php

namespace App\Services;

use App\Models\Book;

class ISBNQueueService
{
    public function readyBooks()
    {
        return Book::where(
            'workflow_status',
            'ready_for_isbn'
        )
            ->orderBy(
                'updated_at'
            )
            ->get();
    }
}