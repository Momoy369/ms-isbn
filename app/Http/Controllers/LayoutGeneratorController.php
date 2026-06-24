<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookSection;
use App\Services\BookLayoutGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\Response;

use function collect;
use function now;
use function storage_path;
use function strip_tags;

class LayoutGeneratorController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()
            ->with([
                'sectionsGenerator:id,book_id,section_type,content,sort_order,title',
                'files' => fn($fileQuery) => $fileQuery
                    ->select('id', 'book_id', 'type', 'is_active')
                    ->where('type', 'cover')
                    ->where('is_active', true),
            ]);

        $search = trim((string) $request->input('q', ''));
        $bookType = (string) $request->input('book_type', '');
        $readiness = (string) $request->input('readiness', '');

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search): void {
                $searchQuery->where('nomor_naskah', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('penulis_1', 'like', "%{$search}%");
            });
        }

        if ($bookType !== '') {
            $query->where('book_type', $bookType);
        }

        $books = $query
            ->latest()
            ->paginate(20);

        $books->getCollection()->transform(function (Book $book) {
            $validation = $this->buildLayoutValidation($book);

            $book->setAttribute('layout_validation', $validation);
            $book->setAttribute('layout_ready', $this->isReadyForLayout($validation));

            return $book;
        });

        if ($readiness !== '') {
            $filtered = $books->getCollection()->filter(function (Book $book) use ($readiness): bool {
                return $readiness === 'ready'
                    ? (bool) $book->getAttribute('layout_ready')
                    : !(bool) $book->getAttribute('layout_ready');
            })->values();

            $books->setCollection($filtered);
        }

        $summary = [
            'listed' => $books->count(),
            'ready' => $books->getCollection()->where('layout_ready', true)->count(),
            'needs_attention' => $books->getCollection()->where('layout_ready', false)->count(),
        ];

        return view(
            'layout-generator.index',
            compact('books', 'summary', 'search', 'bookType', 'readiness')
        );
    }

    public function show(
        Book $book
    ) {
        $book->load(
            'sectionsGenerator',
            'files'
        );

        $validation = $this->buildLayoutValidation($book);
        $isReadyForLayout = $this->isReadyForLayout($validation);

        $totalWords = (int) $book->getWordCount();
        $estimatedPages = (int) $book->getEstimatedPages();

        $validationLabels = [
            'judul' => 'Judul',
            'penulis' => 'Penulis',
            'isbn' => 'ISBN',
            'cover' => 'Cover Aktif',
            'kata_pengantar' => 'Kata Pengantar',
            'tentang_penulis' => 'Tentang Penulis',
            'isi_utama' => 'Isi Utama',
        ];

        $missingRequirements = collect($validation)
            ->filter(fn(bool $passed): bool => $passed === false)
            ->keys()
            ->map(fn(string $key): string => $validationLabels[$key] ?? $key)
            ->values();

        $sectionBreakdown = $book->sectionsGenerator
            ->groupBy('section_type')
            ->map(fn($items): int => $items->count())
            ->sortDesc();

        return view(

            'layout-generator.show',

            compact(
                'book',
                'totalWords',
                'estimatedPages',
                'validation',
                'isReadyForLayout',
                'missingRequirements',
                'sectionBreakdown'
            )

        );
    }

    public function storeSection(
        Book $book,
        Request $request
    ) {
        $allowedSectionTypes = $this->allowedSectionTypesByBook($book);

        $data = $request->validate([
            'section_type' => ['required', 'string', Rule::in($allowedSectionTypes)],
            'title' => ['required', 'string', 'max:255'],
            'heading_level' => ['nullable', 'integer', 'min:1', 'max:6'],
        ]);

        if (
            in_array($data['section_type'], $this->singletonSectionTypes(), true)
            && $book->sectionsGenerator()->where('section_type', $data['section_type'])->exists()
        ) {
            return back()
                ->withErrors([
                    'section_type' => 'Jenis bagian ini hanya boleh satu per naskah.',
                ])
                ->withInput();
        }

        $lastOrder =

            $book
                ->sectionsGenerator()
                ->max('sort_order');

        $book
            ->sectionsGenerator()
            ->create([

                'section_type' =>
                    $data['section_type'],

                'title' =>
                    $data['title'],

                'content' =>
                    '',

                'heading_level' =>
                    $data['heading_level'] ?? 1,

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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $section->update([

            'title' =>
                $data['title'],

            'content' =>
                $data['content'] ?? null

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
        $bookId = $section->book_id;

        $section->delete();

        $this->normalizeSortOrder($bookId);

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

            $this->normalizeSortOrder($section->book_id);
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

            $this->normalizeSortOrder($section->book_id);
        }

        return back();
    }

    public function generate(
        Book $book,
        BookLayoutGeneratorService $generator
    ) {
        $book->load('sectionsGenerator', 'files');

        $validation = $this->buildLayoutValidation($book);

        if (!$this->isReadyForLayout($validation)) {
            return back()->with(
                'warning',
                'Layout belum siap di-generate. Lengkapi checklist validasi terlebih dahulu.'
            );
        }

        $phpWord =
            $generator->build(
                $book
            );

        $directory = storage_path('app/public/layout-exports');
        File::ensureDirectoryExists($directory);

        $safeTitle = $this->safeFileName($book->judul);
        $fileName = 'layout-' . $book->id . '-' . $safeTitle . '-' . now()->format('YmdHis') . '.docx';
        $file = $directory . DIRECTORY_SEPARATOR . $fileName;

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

        $validation = $this->buildLayoutValidation($book);

        if (!$this->isReadyForLayout($validation)) {
            return back()->with(
                'warning',
                'Template DOCX belum bisa dibuat karena naskah belum memenuhi validasi layout.'
            );
        }

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
            '-' .
            $this->safeFileName($book->judul) .
            '-' .
            now()->format('YmdHis') .
            '.docx';

        $directory = storage_path('app/public/layout-exports');
        File::ensureDirectoryExists($directory);

        $path =
            $directory .
            DIRECTORY_SEPARATOR .
            $fileName;

        $template->saveAs(
            $path
        );

        return response()
            ->download(
                $path
            )
            ->deleteFileAfterSend();

    }

    private function buildLayoutValidation(Book $book): array
    {
        $sections = $book->relationLoaded('sectionsGenerator')
            ? $book->sectionsGenerator
            : $book->sectionsGenerator()->get();

        $coverExists = $book->relationLoaded('files')
            ? $book->files->contains(fn($file): bool => $file->type === 'cover' && (bool) $file->is_active)
            : $book->files()->where('type', 'cover')->where('is_active', true)->exists();

        return [
            'judul' => !empty($book->judul),
            'penulis' => !empty($book->penulis_1),
            'isbn' => !empty($book->isbn),
            'cover' => $coverExists,
            'kata_pengantar' => $sections->where('section_type', 'preface')->count() > 0,
            'tentang_penulis' => $sections->whereIn('section_type', ['author', 'about_author'])->count() > 0,
            'isi_utama' => $sections->whereIn('section_type', ['chapter', 'poem'])->count() > 0,
        ];
    }

    private function isReadyForLayout(array $validation): bool
    {
        return collect($validation)->every(fn(bool $item): bool => $item === true);
    }

    private function singletonSectionTypes(): array
    {
        return ['preface', 'foreword', 'author', 'about_author', 'bibliography', 'appendix'];
    }

    private function allowedSectionTypesByBook(Book $book): array
    {
        if ($book->book_type === 'poetry') {
            return ['preface', 'poem', 'author', 'bibliography'];
        }

        if ($book->book_type === 'nonfiction') {
            return ['preface', 'foreword', 'chapter', 'subchapter', 'bibliography', 'appendix', 'author'];
        }

        return ['preface', 'chapter', 'subchapter', 'author', 'bibliography'];
    }

    private function normalizeSortOrder(int $bookId): void
    {
        BookSection::where('book_id', $bookId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (BookSection $item, int $index): void {
                $targetOrder = $index + 1;

                if ((int) $item->sort_order !== $targetOrder) {
                    $item->update(['sort_order' => $targetOrder]);
                }
            });
    }

    private function safeFileName(?string $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return 'dokumen';
        }

        $text = preg_replace('/[^A-Za-z0-9\-_ ]+/', '', $text) ?? '';
        $text = trim(preg_replace('/\s+/', '-', $text) ?? '');

        return $text !== '' ? strtolower($text) : 'dokumen';
    }
}
