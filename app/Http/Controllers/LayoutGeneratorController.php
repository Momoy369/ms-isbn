<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\BookSection;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\LayoutExportService;
use App\Services\BookLayoutGeneratorService;

class LayoutGeneratorController extends Controller
{
    public function index()
    {
        $books = Book::latest()
            ->paginate(20);

        return view(
            'layout-generator.index',
            compact('books')
        );
    }

    public function show(
        Book $book
    ) {

        $validation = [

            'judul' =>
                !empty(
                $book->judul
            ),

            'penulis' =>
                !empty(
                $book->penulis_1
            ),

            'isbn' =>
                !empty(
                $book->isbn
            ),

            'cover' =>
                $book->files()
                    ->where(
                        'type',
                        'cover'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->exists(),

            'kata_pengantar' =>
                $book->sectionsGenerator
                    ->where(
                        'section_type',
                        'preface'
                    )
                    ->count() > 0,

            'tentang_penulis' =>
                $book->sectionsGenerator
                    ->where(
                        'section_type',
                        'author'
                    )
                    ->count() > 0,

        ];

        $isReadyForLayout =
            collect(
                $validation
            )->every(
                    fn($item)
                    => $item
                );

        $book->load(
            'sectionsGenerator'
        );

        $totalWords = 0;

        foreach (
            $book->sectionsGenerator
            as $section
        ) {

            $text =
                strip_tags(
                    $section->content
                );

            $words =
                str_word_count(
                    $text
                );

            $totalWords +=
                $words;
        }

        $estimatedPages =
            ceil(
                $totalWords / 250
            );

        return view(

            'layout-generator.show',

            compact(
                'book',
                'totalWords',
                'estimatedPages',
                'validation',
                'isReadyForLayout'
            )

        );
    }

    public function storeSection(
        Book $book,
        Request $request
    ) {
        $request->validate([

            'section_type' => 'required',

            'title' => 'required'

        ]);

        $lastOrder =

            $book
                ->sectionsGenerator()
                ->max('sort_order');

        $book
            ->sectionsGenerator()
            ->create([

                'section_type' =>
                    $request->section_type,

                'title' =>
                    $request->title,

                'content' =>
                    '',

                'sort_order' =>
                    ($lastOrder ?? 0) + 1

            ]);

        return back()
            ->with(
                'success',
                'Bagian berhasil ditambahkan'
            );
    }

    public function editSection(
        BookSection $section
    ) {
        return view(

            'layout-generator.edit-section',

            compact('section')

        );
    }

    public function updateSection(
        BookSection $section,
        Request $request
    ) {
        $request->validate([

            'title' => 'required',

            'content' => 'nullable'

        ]);

        $section->update([

            'title' =>
                $request->title,

            'content' =>
                $request->content

        ]);

        return redirect()
            ->route(
                'layout-generator.show',
                $section->book_id
            )
            ->with(
                'success',
                'Bagian berhasil diperbarui'
            );
    }

    public function deleteSection(
        BookSection $section
    ) {
        $section->delete();

        return back()
            ->with(
                'success',
                'Bagian berhasil dihapus'
            );
    }

    public function moveUp(
        BookSection $section
    ) {
        $previous = BookSection::where(
            'book_id',
            $section->book_id
        )
            ->where(
                'sort_order',
                '<',
                $section->sort_order
            )
            ->orderByDesc(
                'sort_order'
            )
            ->first();

        if ($previous) {

            $currentOrder =
                $section->sort_order;

            $section->update([

                'sort_order' =>
                    $previous->sort_order

            ]);

            $previous->update([

                'sort_order' =>
                    $currentOrder

            ]);
        }

        return back();
    }

    public function moveDown(
        BookSection $section
    ) {
        $next = BookSection::where(
            'book_id',
            $section->book_id
        )
            ->where(
                'sort_order',
                '>',
                $section->sort_order
            )
            ->orderBy(
                'sort_order'
            )
            ->first();

        if ($next) {

            $currentOrder =
                $section->sort_order;

            $section->update([

                'sort_order' =>
                    $next->sort_order

            ]);

            $next->update([

                'sort_order' =>
                    $currentOrder

            ]);
        }

        return back();
    }

    public function generate(
        Book $book,
        BookLayoutGeneratorService $generator
    ) {
        $phpWord =
            $generator->build(
                $book
            );

        $file =
            storage_path(
                'app/public/layout.docx'
            );

        IOFactory
            ::createWriter(
                $phpWord,
                'Word2007'
            )
            ->save(
                $file
            );

        return response()
            ->download($file)
            ->deleteFileAfterSend();
    }

    // public function generateTemplate(
    //     Book $book
    // ) {
    //     $book->load(
    //         'sectionsGenerator'
    //     );

    //     $template =
    //         new TemplateProcessor(
    //             storage_path(
    //                 'app/templates/template-novel.docx'
    //             )
    //         );

    //     $template->setValue(
    //         'JUDUL',
    //         $book->judul
    //     );

    //     $template->setValue(
    //         'SUBJUDUL',
    //         $book->subjudul ?? ''
    //     );

    //     $template->setValue(
    //         'PENULIS',
    //         $book->penulis_1
    //     );

    //     $template->setValue(
    //         'isbn',
    //         $book->isbn ?? '-'
    //     );

    //     $template->setValue(
    //         'editor',
    //         $book->editor ?? '-'
    //     );

    //     $template->setValue(
    //         'layouter',
    //         $book->layouter ?? '-'
    //     );

    //     $template->setValue(
    //         'designer',
    //         $book->designer ?? '-'
    //     );

    //     $template->setValue(
    //         'penerbit',
    //         'CV MITRA SENTOSA'
    //     );

    //     foreach (
    //         $book->sectionsGenerator
    //             ->sortBy('sort_order')
    //         as $section
    //     ) {
    //         if (
    //             $section->section_type
    //             ===
    //             'preface'
    //         ) {

    //             $preface .=

    //                 strip_tags(
    //                     $section->content
    //                 )

    //                 . "\n\n";

    //             continue;
    //         }

    //         if (
    //             $section->section_type
    //             ===
    //             'chapter'
    //         ) {

    //             $chapters .=

    //                 strtoupper(
    //                     $section->title
    //                 )

    //                 . "\n\n";

    //             $chapters .=

    //                 strip_tags(
    //                     $section->content
    //                 )

    //                 . "\n\n\n";
    //         }
    //     }

    //     $template->setValue(
    //         'KATA_PENGANTAR',
    //         $preface
    //     );

    //     $template->setValue(
    //         'ISI_BUKU',
    //         $chapters
    //     );

    //     $fileName =
    //         'layout-template-' .
    //         $book->id .
    //         '.docx';

    //     $path =
    //         storage_path(
    //             'app/public/' .
    //             $fileName
    //         );

    //     $template->saveAs(
    //         $path
    //     );

    //     return response()
    //         ->download($path)
    //         ->deleteFileAfterSend();
    // }

    public function preview(
        Book $book
    ) {
        $book->load(
            'sectionsGenerator'
        );

        return view(
            'layout-generator.preview',
            compact('book')
        );
    }

    public function generateTemplate(
        Book $book
    ) {
        $book->load(
            'sectionsGenerator'
        );

        $template =
            new TemplateProcessor(

                storage_path(
                    'app/templates/template-novel.docx'
                )

            );

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

                continue;
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
        }

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

        return response()
            ->download(
                $path
            )
            ->deleteFileAfterSend();

    }
}
