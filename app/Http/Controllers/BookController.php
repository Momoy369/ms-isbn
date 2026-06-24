<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Services\ReadinessService;
use App\Services\ApprovalService;
use App\Services\BookActivityService;
use App\Models\User;
use App\Models\PublishingPackage;
use App\Services\AssignmentRecommendationService;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::latest()->get();

        return view(
            'books.index',
            compact('books')
        );
    }

    public function lockMetadata(
        Book $book
    ) {
        $book->update([

            'metadata_locked'
            => true

        ]);

        return back()
            ->with(
                'success',
                'Metadata berhasil dikunci'
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'nomor_naskah' =>
                'required|unique:books',

            'judul' =>
                'required|max:255',

            'penulis_1' =>
                'required|max:255',

            'author_ktp_number' =>
                'required|string|max:32',

            'jumlah_cetak' =>
                'required|integer|min:1'

        ]);

        $author = $this->resolveAuthorFromRequest($request);

        Book::create([

            'nomor_naskah' =>
                $request->nomor_naskah,

            'judul' =>
                $request->judul,

            'subjudul' =>
                $request->subjudul,

            'penulis_1' =>
                $request->penulis_1,

            'author_ktp_number' =>
                $request->author_ktp_number,

            'link_produk' =>
                $request->link_produk,

            'jumlah_cetak' =>
                $request->jumlah_cetak,

            'status' =>
                'draft',

            'author_user_id' =>
                $author?->id

        ]);

        return redirect()
            ->route('books.index')
            ->with(
                'success',
                'Naskah berhasil ditambahkan'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Book $book,
        ReadinessService $readiness
    ) {

        $book->load([

            'activeFiles',

            'audits',

            'assignments',

            'assignmentHistories'

        ]);

        $activities = $book->activities()
            ->latest()
            ->paginate(10, ['*'], 'activities_page');

        $reviews = $book->reviews()
            ->latest()
            ->paginate(10, ['*'], 'reviews_page');

        return view(
            'books.show',
            [

                'book' => $book,

                'activities' => $activities,

                'reviews' => $reviews,

                'readiness' =>
                    $readiness
                        ->calculate($book)

            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book, AssignmentRecommendationService $recommendation)
    {
        $editors = User::where(
            'role',
            'editor'
        )
            ->get()
            ->map(function ($user) {

                $user->workload =
                    \App\Models\BookAssignment::where(
                        'person_name',
                        $user->name
                    )
                        ->whereNull(
                            'completed_at'
                        )
                        ->count();

                return $user;
            });

        $layouters = User::where(
            'role',
            'layouter'
        )
            ->get()
            ->map(function ($user) {

                $user->workload =
                    \App\Models\BookAssignment::where(
                        'person_name',
                        $user->name
                    )
                        ->whereNull(
                            'completed_at'
                        )
                        ->count();

                return $user;
            });

        $designers = User::where(
            'role',
            'designer'
        )
            ->get()
            ->map(function ($user) {

                $user->workload =
                    \App\Models\BookAssignment::where(
                        'person_name',
                        $user->name
                    )
                        ->whereNull(
                            'completed_at'
                        )
                        ->count();

                return $user;
            });

        $recommendedEditor =
            $recommendation
                ->recommendEditor();

        $recommendedLayouter =
            $recommendation
                ->recommendLayouter();

        $packages = PublishingPackage::orderBy('name')->get();

        return view(
            'books.edit',
            compact(
                'book',
                'editors',
                'layouters',
                'designers',
                'recommendedEditor',
                'recommendedLayouter',
                'packages'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Book $book,
        BookActivityService $activity
    ) {

        $request->validate([

            'nomor_naskah' =>

                'required|unique:books,nomor_naskah,' .
                $book->id,

            'judul' =>

                'required|max:255',

            'penulis_1' =>

                'required|max:255',

            'author_ktp_number' =>

                'required|string|max:32',

            'jumlah_cetak' =>

                'required|integer|min:1',

            'editor' =>

                'nullable|exists:users,name',

            'layouter' =>

                'nullable|exists:users,name',

            'designer' =>

                'nullable|exists:users,name',

            'isbn' =>

                'nullable|max:30',

            'publishing_package_id' =>

                'nullable|exists:publishing_packages,id',

        ]);

        $author = $this->resolveAuthorFromRequest($request);

        if (
            $book->metadata_locked
        ) {

            return back()->withErrors(
                'Metadata sudah dikunci'
            );
        }

        $book->update([

            'nomor_naskah' =>
                $request->nomor_naskah,

            'judul' =>
                $request->judul,

            'subjudul' =>
                $request->subjudul,

            'penulis_1' =>
                $request->penulis_1,

            'author_ktp_number' =>
                $request->author_ktp_number,

            'link_produk' =>
                $request->link_produk,

            'jumlah_cetak' =>
                $request->jumlah_cetak,

            'tahun_terbit' => $request->tahun_terbit,

            'isbn' => $request->isbn,

            'editor' => $request->editor,

            'layouter' => $request->layouter,

            'jumlah_halaman' =>
                $request->jumlah_halaman,

            'ukuran_buku' =>
                $request->ukuran_buku,

            'cetakan' =>
                $request->cetakan,

            'designer' =>
                $request->designer,

            'tahun_copyright' =>
                $request->tahun_copyright,

            'book_type' =>
                $request->book_type,

            'publishing_package_id' =>
                $request->publishing_package_id,

            'author_user_id' =>
                $author?->id,

        ]);

        $book->syncAssignments();
        $book->syncPackageItems();

        if ($request->filled('publishing_package_id')) {
            $package = PublishingPackage::find($request->publishing_package_id);

            if ($package && $package->default_print_quantity && empty($book->jumlah_cetak)) {
                $book->update([
                    'jumlah_cetak' => $package->default_print_quantity,
                ]);
            }
        }

        $activity->log(

            $book,

            'Tim Produksi Diperbarui',

            'Editor: ' .
            ($book->editor ?? '-') .

            ', Layouter: ' .
            ($book->layouter ?? '-')

        );


        return redirect()
            ->route('books.index')
            ->with(
                'success',
                'Data berhasil diperbarui'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }

    private function resolveAuthorFromRequest(Request $request): ?User
    {
        if ($request->filled('author_ktp_number')) {
            $authorByKtp = User::where('role', 'author')
                ->where('ktp_number', $request->author_ktp_number)
                ->first();

            if ($authorByKtp) {
                return $authorByKtp;
            }
        }

        return User::where('role', 'author')
            ->where('name', $request->penulis_1)
            ->first();
    }

    public function nextWorkflow(
        Book $book
    ) {
        $book->load('publishingPackage');

        $workflows = $book->workflowSteps();

        $current =
            array_search(
                $book->workflow_status,
                $workflows
            );

        if (
            isset(
            $workflows[
                $current + 1
            ]
        )
        ) {

            $book->audits()
                ->where('passed', false)
                ->exists();

            $nextWorkflow =
                $workflows[
                    $current + 1
                ];

            $data = [

                'workflow_status' =>
                    $nextWorkflow

            ];

            if (
                $nextWorkflow === 'editing'
                &&
                !$book->tanggal_mulai_editing
            ) {

                $data[
                    'tanggal_mulai_editing'
                ] = now();

            }

            if (
                $nextWorkflow === 'layout'
                &&
                !$book->tanggal_mulai_layout
            ) {

                $data[
                    'tanggal_mulai_layout'
                ] = now();

            }

            if (
                $nextWorkflow === 'cover_design'
                &&
                !$book->tanggal_mulai_cover
            ) {

                $data[
                    'tanggal_mulai_cover'
                ] = now();

            }

            if (
                $nextWorkflow === 'acc_penulis'
                &&
                !$book->tanggal_acc_penulis
            ) {

                $data[
                    'tanggal_acc_penulis'
                ] = now();

            }

            $book->update(
                $data
            );

            app(
                BookActivityService::class
            )->log(

                    $book,

                    'Workflow Berubah',

                    $nextWorkflow

                );
        }

        return back();
    }

    public function syncAssignments(
        Book $book
    ) {
        $book->syncAssignments();

        return back()->with(

            'success',

            'Tim produksi berhasil disinkronkan'

        );
    }

    public function approve(
        Book $book,
        string $type,
        ApprovalService $service,
        BookActivityService $activity,
        Request $request
    ) {
        $person =
            auth()->user()->name
            ?? 'Administrator';

        $service->approve(

            $book,

            $type,

            $person

        );

        $activity->log(

            $book,

            'ISBN Approved',

            $request->isbn

        );

        return back()->with(

            'success',

            'Approval berhasil'

        );
    }

    public function submitISBN(
        Book $book,
        BookActivityService $activity
    ) {
        $book->update([

            'workflow_status' =>
                'isbn_submitted',

            'tanggal_pengajuan_isbn' =>
                now()

        ]);

        $activity->log(

            $book,

            'Submit ISBN',

            'Naskah diajukan ke Perpusnas'

        );

        return back()->with(

            'success',

            'Buku berhasil diajukan ke ISBN'

        );
    }

    public function approveISBN(
        Request $request,
        Book $book
    ) {
        $request->validate([

            'isbn' =>
                'required',

            'tanggal' =>
                'required|date'

        ]);

        $book->update([

            'isbn' =>
                $request->isbn,

            'tanggal_isbn_terbit' =>
                $request->tanggal,

            'workflow_status' =>
                'isbn_approved'

        ]);

        return back()->with(

            'success',

            'ISBN berhasil diterbitkan'

        );
    }

    public function finishBook(
        Book $book,
        BookActivityService $activity
    ) {
        $book->update([

            'workflow_status' =>
                'selesai'

        ]);

        $activity->log(

            $book,

            'Produksi Selesai'

        );

        return back()->with(

            'success',

            'Produksi buku selesai'

        );
    }

    public function autoAssign(
        Book $book,
        AssignmentRecommendationService $recommendation,
        BookActivityService $activity
    ) {
        $editor =
            $recommendation
                ->leastLoadedEditor();

        $layouter =
            $recommendation
                ->leastLoadedLayouter();

        $book->update([

            'editor' =>
                $editor?->name,

            'layouter' =>
                $layouter?->name

        ]);

        $book->syncAssignments();

        $activity->log(

            $book,

            'Auto Assignment',

            'Editor: ' .
            ($editor?->name ?? '-') .

            ', Layouter: ' .
            ($layouter?->name ?? '-')

        );

        return back()->with(

            'success',

            'Tim produksi berhasil ditentukan otomatis'

        );
    }

    public function authorApproval(
        Book $book,
        BookActivityService $activity
    ) {

        if (
            $book->penulis_1
            !==
            auth()->user()->name
        ) {

            abort(403);

        }

        $book->update([

            'workflow_status' =>
                'audit_isbn',

            'tanggal_acc_penulis' =>
                now()

        ]);

        $activity->log(

            $book,

            'ACC Penulis',

            'Naskah disetujui penulis'

        );

        return back()->with(

            'success',

            'ACC Penulis berhasil'

        );
    }
}
