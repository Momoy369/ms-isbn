<?php

namespace App\Services;

use App\Models\Book;

class DocumentComparisonService
{
    public function compare(Book $book)
    {
        return [

            'title_page_match' =>
                $this->compareTitlePage($book),

            'title_page_author_match' =>
                $this->compareTitlePageAuthor($book),

            'title_page_subtitle_match' =>
                $this->compareTitlePageSubtitle($book),

            'copyright_match' =>
                $this->compareCopyright($book),

            'copyright_author_match' =>
                $this->checkCopyrightAuthor($book),

            'request_letter_match' =>
                $this->compareRequestLetter($book)

        ];
    }

    private function checkTitle(
        Book $book
    ) {
        return !empty(
            $book->judul
        );
    }

    protected $reader;

    public function __construct(
        DocxMetadataReaderService $reader
    ) {
        $this->reader = $reader;
    }

    private function getCopyrightText(
        Book $book
    ) {
        $file =
            $book->getActiveFile(
                'copyright'
            );

        if (!$file) {
            return null;
        }

        return $this->reader
            ->extractText(
                storage_path(
                    'app/public/' .
                    $file->file_path
                )
            );
    }

    private function checkCopyrightTitle(
        Book $book
    ) {
        $text =
            $this->getCopyrightText(
                $book
            );

        if (!$text) {
            return false;
        }

        return str_contains(

            strtolower($text),

            strtolower(
                $book->judul
            )

        );
    }

    private function checkCopyrightAuthor(
        Book $book
    ) {
        $text =
            $this->getCopyrightText(
                $book
            );

        if (!$text) {
            return false;
        }

        return str_contains(

            strtolower($text),

            strtolower(
                $book->penulis_1
            )

        );
    }

    private function getDocMetadata(
        Book $book,
        string $type
    ) {
        $file =
            $book->getActiveFile(
                $type
            );

        if (!$file) {
            return null;
        }

        return $this->reader
            ->extractMetadata(

                storage_path(
                    'app/public/' .
                    $file->file_path
                )

            );
    }

    private function compareTitlePage(
        Book $book
    ) {
        $metadata =
            $this->getDocMetadata(
                $book,
                'halaman_judul'
            );

        if (!$metadata) {
            return false;
        }

        return
            strtolower(
                trim(
                    $metadata['title']
                )
            )
            ===
            strtolower(
                trim(
                    $book->judul
                )
            );
    }

    private function compareCopyright(
        Book $book
    ) {
        $metadata =
            $this->getDocMetadata(
                $book,
                'copyright'
            );

        if (!$metadata) {
            return false;
        }

        return
            strtolower(
                trim(
                    $metadata['title']
                )
            )
            ===
            strtolower(
                trim(
                    $book->judul
                )
            );
    }

    private function compareRequestLetter(
        Book $book
    ) {
        $metadata =
            $this->getDocMetadata(
                $book,
                'surat_permohonan'
            );

        if (!$metadata) {
            return false;
        }

        return
            str_contains(

                strtolower(
                    json_encode(
                        $metadata
                    )
                ),

                strtolower(
                    $book->judul
                )

            );
    }

    private function compareTitlePageAuthor(
        Book $book
    ) {
        $metadata =
            $this->getDocMetadata(
                $book,
                'halaman_judul'
            );

        if (!$metadata) {
            return false;
        }

        return
            strtolower(
                trim(
                    $metadata['author']
                )
            )
            ===
            strtolower(
                trim(
                    $book->penulis_1
                )
            );
    }

    private function compareTitlePageSubtitle(
        Book $book
    ) {
        $metadata =
            $this->getDocMetadata(
                $book,
                'halaman_judul'
            );

        if (!$metadata) {
            return true;
        }

        if (
            !$book->subjudul
        ) {
            return true;
        }

        return
            strtolower(
                trim(
                    $metadata['subtitle']
                )
            )
            ===
            strtolower(
                trim(
                    $book->subjudul
                )
            );
    }
}