<?php

namespace App\Services;

use App\Models\Book;
use App\Models\AuditResult;

class IsbnAuditService
{
    public function run(Book $book)
    {
        AuditResult::where(
            'book_id',
            $book->id
        )->delete();

        $this->checkCover($book);

        $this->checkSKK($book);

        $this->checkDocuments($book);

        $this->checkKataPengantar($book);

        $this->checkTitleMatch($book);
    }

    private function checkCover(Book $book)
    {
        $exists =
            $book->files()
                ->where(
                    'type',
                    'cover'
                )
                ->exists();

        AuditResult::create([

            'book_id' =>
                $book->id,

            'rule' =>
                'cover_exists',

            'passed' =>
                $exists,

            'message' =>
                $exists
                ? 'Cover tersedia'
                : 'Cover belum diupload'

        ]);
    }

    private function checkSKK(Book $book)
    {
        $exists =
            $book->files()
                ->where(
                    'type',
                    'skk'
                )
                ->exists();

        AuditResult::create([

            'book_id' =>
                $book->id,

            'rule' =>
                'skk_exists',

            'passed' =>
                $exists,

            'message' =>
                $exists
                ? 'SKK tersedia'
                : 'SKK belum diupload'

        ]);
    }

    private function checkDocuments(Book $book)
    {
        $required = [

            'halaman_judul',

            'surat_permohonan',

            'copyright'

        ];

        foreach (
            $required as $type
        ) {

            $exists =
                $book->files()
                    ->where(
                        'type',
                        $type
                    )
                    ->exists();

            AuditResult::create([

                'book_id' =>
                    $book->id,

                'rule' =>
                    $type,

                'passed' =>
                    $exists,

                'message' =>
                    $exists
                    ? "$type tersedia"
                    : "$type belum dibuat"

            ]);
        }
    }

    private function checkKataPengantar(
        Book $book
    ) {
        $analysis =
            $book->analysis;

        if (!$analysis) {
            return;
        }

        $text =
            strtolower(
                $analysis->kata_pengantar
            );

        $forbidden = [

            'kritik dan saran',

            'banyak kekurangan',

            'mohon masukan'

        ];

        foreach (
            $forbidden as $word
        ) {

            if (
                str_contains(
                    $text,
                    $word
                )
            ) {

                AuditResult::create([

                    'book_id' =>
                        $book->id,

                    'rule' =>
                        'kata_pengantar',

                    'passed' =>
                        false,

                    'message' =>
                        'Ditemukan: ' .
                        $word

                ]);

                return;
            }
        }

        AuditResult::create([

            'book_id' =>
                $book->id,

            'rule' =>
                'kata_pengantar',

            'passed' =>
                true,

            'message' =>
                'Kata pengantar aman'

        ]);
    }

    private function checkTitleMatch(
        Book $book
    ) {
        $title =
            strtolower(
                trim(
                    $book->judul
                )
            );

        foreach (
            $book->files as $file
        ) {

            if (
                !$file->content
            ) {
                continue;
            }

            $content =
                strtolower(
                    $file->content->content
                );

            $passed =
                str_contains(
                    $content,
                    $title
                );

            AuditResult::create([

                'book_id' =>
                    $book->id,

                'rule' =>
                    'title_match_' .
                    $file->type,

                'passed' =>
                    $passed,

                'message' =>
                    $passed
                    ? 'Judul cocok'
                    : 'Judul tidak ditemukan'

            ]);
        }
    }

    public function calculateStatus(
        Book $book
    ) {
        return !$book->audits()
            ->where(
                'passed',
                false
            )
            ->exists();
    }
}
