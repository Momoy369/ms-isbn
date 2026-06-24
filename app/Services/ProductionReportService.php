<?php

namespace App\Services;

use App\Models\Book;

class ProductionReportService
{
    public function monthly(
        int $year,
        int $month
    ) {
        return [

            'books_in' =>

                Book::whereYear(
                    'created_at',
                    $year
                )
                    ->whereMonth(
                        'created_at',
                        $month
                    )
                    ->count(),

            'completed' =>

                Book::where(
                    'workflow_status',
                    'selesai'
                )
                    ->whereYear(
                        'updated_at',
                        $year
                    )
                    ->whereMonth(
                        'updated_at',
                        $month
                    )
                    ->count(),

            'isbn_approved' =>

                Book::whereNotNull(
                    'tanggal_isbn_terbit'
                )
                    ->whereYear(
                        'tanggal_isbn_terbit',
                        $year
                    )
                    ->whereMonth(
                        'tanggal_isbn_terbit',
                        $month
                    )
                    ->count()

        ];
    }
}