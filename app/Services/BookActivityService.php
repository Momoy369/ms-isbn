<?php

namespace App\Services;

use App\Models\Book;

class BookActivityService
{
    public function log(
        Book $book,
        string $activity,
        string $description = null
    ) {
        $book->activities()
            ->create([

                'activity' =>
                    $activity,

                'description' =>
                    $description

            ]);
    }
}