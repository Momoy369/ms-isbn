<?php

namespace App\Http\Controllers;

use App\Services\ISBNQueueService;

class ISBNQueueController
{
    public function index(
        ISBNQueueService $service
    )
    {
        $books =
            $service
                ->readyBooks();

        return view(
            'isbn.queue',
            compact(
                'books'
            )
        );
    }
}