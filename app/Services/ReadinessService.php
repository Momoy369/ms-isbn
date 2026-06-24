<?php

namespace App\Services;

use App\Models\Book;

class ReadinessService
{
    public function calculate(
        Book $book
    ) {
        $total =
            $book->audits()
                ->count();

        $passed =
            $book->audits()
                ->where(
                    'passed',
                    true
                )
                ->count();

        if ($total === 0) {
            return 0;
        }

        return round(
            ($passed / $total) * 100
        );
    }
}