<?php

namespace App\Services;

use App\Models\Book;
use PhpOffice\PhpWord\IOFactory;

class MetadataExtractorService
{
    public function analyze(Book $book)
    {
        $naskah =
            $book->getActiveFile(
                'naskah_final'
            );

        if (!$naskah) {
            throw new \Exception(
                'Naskah Final belum diupload'
            );
        }

        $fullPath = storage_path(
            'app/public/' .
            $naskah->file_path
        );

        if (!file_exists($fullPath)) {
            throw new \Exception(
                'File naskah tidak ditemukan'
            );
        }

        $phpWord = IOFactory::load($fullPath);

        $elements =
            $this->extractElements(
                $phpWord
            );

        $lines =
            $this->cleanLines(
                $elements
            );

        $text = '';

        $lines = explode("\n", $text);

        $lines = array_map(
            'trim',
            $lines
        );

        $lines = array_filter(
            $lines
        );

        $lines = array_values(
            $lines
        );

        $previewLines = array_slice(
            $lines,
            0,
            30
        );

        $title =
            $this->detectTitle(
                $lines
            );

        $author = $this->detectAuthor(
            $previewLines
        );

        $subtitle = $this->detectSubtitle(
            $previewLines,
            $title,
            $author
        );

        if (
            $book->metadata_locked
        ) {

            return [

                'title' => $title,

                'subtitle' => $subtitle,

                'author' => $author,

                'locked' => true

            ];
        }

        $book->update([

            'judul' =>
                $title
                ?: $book->judul,

            'subjudul' =>
                $subtitle
                ?: $book->subjudul,

            'penulis_1' =>
                $author
                ?: $book->penulis_1

        ]);

        return [

            'title' =>
                $title,

            'subtitle' =>
                $subtitle,

            'author' =>
                $author,

            'preview' =>
                $previewLines

        ];
    }

    private function detectTitleFromStyle(
        array $elements
    ) {
        $best = null;

        $bestScore = 0;

        foreach (
            $elements as $element
        ) {

            $score = 0;

            $length =
                mb_strlen(
                    $element['text']
                );

            if (
                $length > 10
                &&
                $length < 200
            ) {
                $score += 10;
            }

            if (
                $element['bold']
            ) {
                $score += 30;
            }

            if (
                $element['size']
            ) {
                $score +=
                    $element['size'];
            }

            if (
                $score >
                $bestScore
            ) {

                $bestScore =
                    $score;

                $best =
                    $element['text'];
            }
        }

        return $best;
    }

    private function detectAuthors(
        array $lines
    ) {
        $authors = [];

        foreach ($lines as $line) {

            if (
                preg_match(
                    '/^[A-Za-zÀ-ÿ\s\.\&]+$/u',
                    $line
                )
            ) {

                if (
                    str_word_count(
                        $line
                    ) >= 2
                    &&
                    str_word_count(
                        $line
                    ) <= 6
                ) {

                    if (
                        !str_contains(
                            strtolower($line),
                            'copyright'
                        )
                    ) {

                        $authors[] =
                            trim($line);
                    }
                }
            }
        }

        return array_unique(
            $authors
        );
    }

    private function detectTitle(
        array $lines
    ) {
        foreach ($lines as $line) {

            $lower =
                strtolower($line);

            if (
                str_contains(
                    $lower,
                    'copyright'
                )
            ) {
                continue;
            }

            if (
                str_contains(
                    $lower,
                    'isbn'
                )
            ) {
                continue;
            }

            if (
                str_contains(
                    $lower,
                    'cv mitra sentosa'
                )
            ) {
                continue;
            }

            if (
                mb_strlen($line) > 10
                &&
                mb_strlen($line) < 150
            ) {

                return $line;
            }
        }

        return null;
    }

    private function detectAuthor(
        array $lines
    ) {
        foreach ($lines as $line) {

            if (
                preg_match(
                    '/^[A-Za-zÀ-ÿ\s\.,]+$/u',
                    $line
                )
            ) {

                $words =
                    str_word_count(
                        $line
                    );

                if (
                    $words >= 2
                    &&
                    $words <= 6
                ) {

                    return $line;
                }
            }
        }

        return null;
    }

    private function detectSubtitle(
        array $lines,
        ?string $title,
        ?string $author
    ) {
        foreach ($lines as $line) {

            if (
                $line !== $title
                &&
                $line !== $author
                &&
                mb_strlen($line) > 20
            ) {

                return $line;
            }
        }

        return null;
    }

    private function extractElements(
        $phpWord
    ) {
        $elements = [];

        foreach (
            $phpWord->getSections()
            as $section
        ) {

            foreach (
                $section->getElements()
                as $element
            ) {

                $text = '';

                if (
                    $element instanceof
                    \PhpOffice\PhpWord\Element\Text
                ) {

                    $text =
                        trim(
                            $element->getText()
                        );
                } elseif (
                    $element instanceof
                    \PhpOffice\PhpWord\Element\TextRun
                ) {

                    foreach (
                        $element->getElements()
                        as $child
                    ) {

                        if (
                            method_exists(
                                $child,
                                'getText'
                            )
                        ) {

                            $text .=
                                $child->getText();
                        }
                    }

                    $text = trim($text);
                }

                if (!$text) {
                    continue;
                }

                $elements[] = [

                    'text' =>
                        $text

                ];
            }
        }

        return $elements;
    }

    private function cleanLines(
        array $elements
    ) {
        $lines = [];

        foreach ($elements as $element) {

            $text =
                trim(
                    html_entity_decode(
                        $element['text']
                    )
                );

            if (!$text) {
                continue;
            }

            $lines[] = $text;
        }

        return $lines;
    }
}