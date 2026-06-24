<?php

namespace App\Services;

use App\Models\Book;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;

class LayoutExportService
{
    public function build(
        Book $book
    ) {
        $phpWord =
            new PhpWord();

        $section =
            $phpWord->addSection();

        $this->buildCoverPage(
            $section,
            $book
        );

        $section->addPageBreak();

        $this->buildCopyrightPage(
            $section,
            $book
        );

        $this->buildSections(
            $section,
            $book
        );

        return $phpWord;
    }

    private function buildCoverPage(
        $section,
        Book $book
    ) {
        $section->addText(
            $book->judul,
            [
                'bold' => true,
                'size' => 24
            ],
            [
                'alignment' => 'center'
            ]
        );

        $section->addTextBreak(2);

        if ($book->subjudul) {

            $section->addText(

                $book->subjudul,

                [
                    'size' => 16
                ],

                [
                    'alignment' => 'center'
                ]

            );
        }

        $section->addTextBreak(8);

        $section->addText(

            $book->penulis_1,

            [
                'bold' => true,
                'size' => 16
            ],

            [
                'alignment' => 'center'
            ]

        );

        $section->addTextBreak(2);

        $section->addText(

            'CV MITRA SENTOSA',

            [
                'bold' => true
            ],

            [
                'alignment' => 'center'
            ]

        );
    }

    private function buildCopyrightPage(
        $section,
        Book $book
    ) {
        $section->addText(

            $book->judul,

            [
                'bold' => true
            ]

        );

        $section->addText(

            'Copyright © ' .
            ($book->penulis_1 ?? '-') .
            ', ' .
            now()->year

        );

        $section->addTextBreak();

        $section->addText(
            'ISBN: ' .
            ($book->isbn ?? '-')
        );

        $section->addTextBreak();

        $section->addText(
            'Penulis: ' .
            ($book->penulis_1 ?? '-')
        );

        $section->addText(
            'Editor: ' .
            ($book->editor ?? '-')
        );

        $section->addText(
            'Penata Letak: ' .
            ($book->layouter ?? '-')
        );

        $section->addText(
            'Desain Sampul: ' .
            ($book->designer ?? '-')
        );

        $section->addTextBreak();

        $section->addText(
            'CV MITRA SENTOSA'
        );
    }

    private function buildSections(
        $section,
        Book $book
    ) {
        $book->load(
            'sectionsGenerator'
        );

        $chapterNumber = 1;
        $subChapterNumber = 1;

        foreach (
            $book->sectionsGenerator
                ->sortBy('sort_order')
            as $item
        ) {

            $section->addPageBreak();

            if (
                $item->section_type === 'chapter'
            ) {

                $section->addPageBreak();

                $section->addText(

                    'BAB ' . $chapterNumber,

                    [
                        'bold' => true,
                        'size' => 16
                    ],

                    [
                        'alignment' => 'center'
                    ]

                );

                $section->addTextBreak();

                $section->addText(

                    strtoupper(
                        $item->title
                    ),

                    [
                        'bold' => true,
                        'size' => 18
                    ],

                    [
                        'alignment' => 'center'
                    ]

                );

                $section->addTextBreak(2);

                $chapterNumber++;

                $subChapterNumber = 1;
            }

            if (
                $item->section_type === 'subchapter'
            ) {

                $section->addText(

                    ($chapterNumber - 1)
                    . '.'
                    . $subChapterNumber
                    . ' '
                    . $item->title,

                    [
                        'bold' => true,
                        'size' => 13
                    ]

                );

                $section->addTextBreak();

                $content =
                    str_replace(
                        '&nbsp;',
                        ' ',
                        $item->content
                    );

                Html::addHtml(
                    $section,
                    $content,
                    false,
                    false
                );

                $subChapterNumber++;

                continue;
            }

            $section->addTextBreak();

            $content = $item->content;

            $content =
                str_replace(
                    '&nbsp;',
                    ' ',
                    $item->content
                );

            Html::addHtml(
                $section,
                $content,
                false,
                false
            );
        }
    }

    public function generateTemplate(
        Book $book
    ) {
        $template =
            new TemplateProcessor(

                storage_path(
                    'app/templates/template-novel.docx'
                )

            );

        $cover = $book->getActiveFile('cover');

        $template->setValue(
            'JUDUL',
            $book->judul
        );

        $template->setValue(
            'SUBJUDUL',
            $book->subjudul ?? ''
        );

        $template->setValue(
            'PENULIS',
            $book->penulis_1
        );

        $template->setValue(
            'isbn',
            $book->isbn ?? '-'
        );

        $template->setValue(
            'editor',
            $book->editor ?? '-'
        );

        $template->setValue(
            'layouter',
            $book->layouter ?? '-'
        );

        $template->setValue(
            'designer',
            $book->designer ?? '-'
        );

        $kataPengantar = '';
        $isiBuku = '';

        foreach (
            $book->sectionsGenerator
                ->sortBy('sort_order')
            as $section
        ) {
            if (
                $section->section_type
                ===
                'preface'
            ) {

                $kataPengantar .=
                    strip_tags(
                        $section->content
                    );

            }

            if (
                $section->section_type
                ===
                'chapter'
            ) {

                $isiBuku .=

                    "\n\n"

                    .

                    strtoupper(
                        $section->title
                    )

                    .

                    "\n\n";

            }

            $isiBuku .=

                strip_tags(
                    $section->content
                )

                .

                "\n\n";

            $template->setValue(
                'KATA_PENGANTAR',
                $kataPengantar
            );

            $template->setValue(
                'ISI_BUKU',
                $isiBuku
            );

            $fileName =
                'layout-' .
                $book->id .
                '.docx';

            $path =
                storage_path(
                    'app/public/' .
                    $fileName
                );

            $template->saveAs(
                $path
            );

            $template->setImageValue(
                'cover',
                [
                    'path' =>
                        storage_path(
                            'app/public/' .
                            $cover->file_path
                        ),
                    'width' => 350,
                    'height' => 500
                ]
            );

        }
    }

}