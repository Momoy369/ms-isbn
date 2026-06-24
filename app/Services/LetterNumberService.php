<?php

namespace App\Services;

use App\Models\Book;

class LetterNumberService
{
    public function generate()
    {
        $year = now()->year;

        $month =
            $this->romanMonth(
                now()->month
            );

        $count = Book::whereYear(
            'created_at',
            $year
        )->whereNotNull(
                'nomor_surat'
            )->count() + 1;

        return sprintf(
            '%03d/MSP/ISBN/%s/%s',
            $count,
            $month,
            $year
        );
    }

    private function romanMonth(
        int $month
    ) {
        return [

            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'

        ][$month];
    }
}