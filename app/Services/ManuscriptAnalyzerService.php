<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DocumentContent;
use PhpOffice\PhpWord\IOFactory;
use App\Models\ManuscriptSection;

class ManuscriptAnalyzerService
{
    public function analyze(Book $book)
    {
        $file = $book->getActiveFile(
            'naskah_final'
        );

        if (!$file) {
            throw new \Exception(
                'Naskah final tidak ditemukan'
            );
        }

        $path = storage_path(
            'app/public/' .
            $file->file_path
        );

        if (!file_exists($path)) {
            throw new \Exception(
                'File tidak ditemukan'
            );
        }

        $phpWord = IOFactory::load($path);

        $lines = $this->extractLines(
            $phpWord
        );

        return $this->processLines(
            $book,
            $file->id,
            $lines
        );
    }

    private function extractLines(
        $phpWord
    ) {
        $lines = [];

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

                $text = html_entity_decode(
                    $text
                );

                if ($text) {

                    $lines[] = $text;
                }
            }
        }

        return array_values(
            array_filter($lines)
        );
    }

    private function processLines(
        Book $book,
        int $bookFileId,
        array $lines
    ) {
        $metadata =
            $this->extractMetadata(
                $lines
            );

        $metadata =
            $this->extractMetadata(
                $lines
            );

        $this->saveMetadata(
            $book,
            $metadata
        );

        $this->saveSections(
            $book,
            $lines
        );

        DocumentContent::updateOrCreate(

            [
                'book_file_id' =>
                    $bookFileId
            ],

            [
                'content' =>
                    implode(
                        "\n",
                        $lines
                    )
            ]

        );

        return $metadata;
    }

    private function saveSections(
        Book $book,
        array $lines
    ) {

        $book->sections()->delete();

        $sections = [

            'kata pengantar' => 'preface',
            'prakata' => 'preface',
            'mukadimah' => 'preface',

            'abstrak' => 'abstract',

            'prolog' => 'prologue',

            'sambutan' => 'greeting',

            'daftar isi' => 'table_of_contents'

        ];

        foreach (
            $sections as $keyword => $type
        ) {

            $this->detectAndSaveSection(
                $book,
                $lines,
                $keyword,
                $type
            );
        }
    }

    private function detectAndSaveSection(
        Book $book,
        array $lines,
        string $keyword,
        string $sectionType
    ) {
        $foundIndex = null;

        foreach ($lines as $index => $line) {

            $cleanLine = strtolower(
                trim($line)
            );

            if (
                $cleanLine === strtolower($keyword)
            ) {

                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === null) {
            return;
        }

        $content = [];

        for (
            $i = $foundIndex + 1;
            $i < count($lines);
            $i++
        ) {

            $current = trim(
                $lines[$i]
            );

            if (!$current) {
                continue;
            }

            /*
            |--------------------------------------------------
            | Untuk section selain daftar isi:
            | berhenti saat menemukan BAB I
            |--------------------------------------------------
            */

            if (
                $sectionType !== 'table_of_contents'
                &&
                preg_match(
                    '/^BAB\s*([0-9IVX]+)/i',
                    $current
                )
            ) {
                break;
            }

            /*
            |--------------------------------------------------
            | Berhenti jika menemukan heading section lain
            |--------------------------------------------------
            */

            if (
                in_array(
                    strtolower($current),
                    [
                        'kata pengantar',
                        'prakata',
                        'mukadimah',
                        'abstrak',
                        'prolog',
                        'sambutan',
                        'daftar isi'
                    ]
                )
            ) {
                break;
            }

            if (
                $sectionType === 'table_of_contents'
            ) {

                if (
                    preg_match(
                        '/^BAB\s*([0-9IVX]+)/i',
                        $current
                    )
                ) {

                    $content[] = $current;

                    continue;
                }

                if (
                    count($content) > 0
                ) {
                    break;
                }

                continue;
            }

            $content[] = $current;
        }

        /*
        |--------------------------------------------------
        | Jangan simpan jika content kosong
        |--------------------------------------------------
        */

        if (empty($content)) {
            return;
        }

        ManuscriptSection::create([

            'book_id' =>
                $book->id,

            'section_type' =>
                $sectionType,

            'content' =>
                implode(
                    "\n",
                    $content
                )

        ]);
    }

    private function saveMetadata(
        Book $book,
        array $metadata
    ) {
        $book->update([

            'judul' =>
                $metadata['title'],

            'subjudul' =>
                $metadata['subtitle'],

            'penulis_1' =>
                $metadata['authors'][0]
                ?? null,

            'penulis_2' =>
                $metadata['authors'][1]
                ?? null,

            'penulis_3' =>
                $metadata['authors'][2]
                ?? null,

            'editor' =>
                $metadata['editor'],

            'layouter' =>
                $metadata['layouter'],

            'designer' =>
                $metadata['cover_designer'],

            'isbn' =>
                $metadata['isbn']

        ]);
    }

    private function extractMetadata(
        array $lines
    ) {
        return [

            'title' =>
                $this->detectTitle(
                    $lines
                ),

            'subtitle' =>
                $this->detectSubtitle(
                    $lines
                ),

            'authors' =>
                $this->detectAuthors(
                    $lines
                ),

            'editor' =>
                $this->detectValueAfter(
                    $lines,
                    'Editor:'
                ),

            'layouter' =>
                $this->detectValueAfter(
                    $lines,
                    'Penata Letak:'
                ),

            'cover_designer' =>
                $this->detectValueAfter(
                    $lines,
                    'Desain Sampul:'
                ),

            'publisher' =>
                $this->detectValueAfter(
                    $lines,
                    'Penerbit:'
                ),

            'isbn' =>
                $this->detectISBN(
                    $lines
                )

        ];
    }

    private function detectTitle(
        array $lines
    ) {
        return $lines[0] ?? null;
    }

    private function detectSubtitle(
        array $lines
    ) {
        return null;
    }

    private function detectAuthors(
        array $lines
    ) {
        $authors = [];

        for ($i = 1; $i <= 10; $i++) {

            if (!isset($lines[$i])) {
                continue;
            }

            $line = trim($lines[$i]);

            $lower = strtolower($line);

            if (
                $line === '&'
            ) {
                continue;
            }

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
                    'cetakan'
                )
            ) {
                continue;
            }

            if (
                str_contains(
                    $lower,
                    'hlm'
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
                strtoupper($line) === $line
            ) {
                continue;
            }

            $wordCount =
                str_word_count($line);

            if (
                $wordCount >= 2 &&
                $wordCount <= 5
            ) {

                $authors[] = $line;
            }
        }

        return array_values(
            array_unique(
                $authors
            )
        );
    }

    private function detectValueAfter(
        array $lines,
        string $label
    ) {
        foreach (
            $lines as $index => $line
        ) {

            if (
                trim($line) === $label
            ) {

                return
                    $lines[
                        $index + 1
                    ] ?? null;
            }
        }

        return null;
    }

    private function detectISBN(
        array $lines
    ) {
        foreach (
            $lines as $line
        ) {

            if (
                str_contains(
                    strtoupper($line),
                    'ISBN'
                )
            ) {

                return trim(
                    str_replace(
                        'ISBN:',
                        '',
                        $line
                    )
                );
            }
        }

        return null;
    }

}