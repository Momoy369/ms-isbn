<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Services\ReadinessService;
use App\Services\ApprovalService;
use App\Services\BookActivityService;
use App\Models\AuthorInvoice;
use App\Models\User;
use App\Models\PublishingPackage;
use App\Services\AssignmentRecommendationService;
use App\Services\BookCompletionOrchestratorService;
use App\Services\BookWorkflowActionService;
use App\Services\BookWorkflowGuardService;
use App\Services\PerpusnasIsbnService;

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

        $book = Book::create([

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

        if ($book->publishing_package_id) {
            $book->load('publishingPackage');
            AuthorInvoice::createPackageInvoice($book);
        }

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
        ReadinessService $readiness,
        BookWorkflowGuardService $workflowGuard
    ) {

        $book->load([

            'activeFiles',

            'audits',

            'assignments',

            'assignmentHistories',

            'publishingPackage',

            'authorInvoices',

            'orders.invoice',

            'orders.user',

            'approvals'

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
                        ->calculate($book),

                'workflowUi' =>
                    $workflowGuard
                        ->evaluate(
                            $book,
                            auth()->user()
                        )

            ]
        );
    }

    public function workflowActionState(
        Book $book,
        BookWorkflowGuardService $workflowGuard
    ) {
        $book->load('approvals');

        return response()->json(
            $workflowGuard->evaluate(
                $book,
                auth()->user()
            )
        );
    }

    public function executePrimaryWorkflowAction(
        Request $request,
        Book $book,
        BookWorkflowActionService $workflowAction
    ) {
        $result = $workflowAction->executePrimary(
            $book,
            auth()->user(),
            $request->only(['isbn', 'tanggal'])
        );

        $status = (string) ($result['status'] ?? 'info');
        $message = (string) ($result['message'] ?? 'Aksi diproses.');

        return back()->with($status, $message);
    }

    public function prepareIsbnWorkflow(
        Book $book,
        BookWorkflowActionService $workflowAction
    ) {
        $result = $workflowAction->prepareIsbn(
            $book,
            auth()->user()
        );

        $status = (string) ($result['status'] ?? 'info');
        $message = (string) ($result['message'] ?? 'Prepare ISBN diproses.');

        return back()->with($status, $message);
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

            'selling_price' =>

                'nullable|numeric|min:0',

            'revision_fee_amount' =>

                'nullable|numeric|min:0',

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

            'selling_price' => $request->selling_price,

            'revision_fee_amount' => $request->revision_fee_amount,

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

            $book->load('publishingPackage');
            AuthorInvoice::createPackageInvoice($book);
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

        if (!$book->hasPaidInitialPackageInvoice()) {
            return back()->with('warning', $book->dpPaymentWarningMessage());
        }

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

            if ($nextWorkflow === 'selesai') {
                app(BookCompletionOrchestratorService::class)->handle($book, 'workflow_next');
            }

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

            'Legacy Approval Endpoint',

            'Approval type: ' . $type

        );

        return back()->with(

            'success',

            'Approval berhasil'

        )->with(
                'info',
                'Catatan: endpoint approval lama sedang dalam masa transisi. Gunakan Action Center untuk alur utama.'
            );
    }

    public function submitISBN(
        Book $book,
        BookActivityService $activity
    ) {
        if (!$book->canSubmitIsbnToPerpusnas()) {
            return back()->with('warning', 'Submit ISBN hanya bisa dilakukan saat status buku berada di READY FOR ISBN.');
        }

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
        Book $book,
        PerpusnasIsbnService $perpusnas,
        BookActivityService $activity
    ) {
        if (!$book->canApproveIsbnIssued()) {
            return back()->with('warning', 'Terbitkan ISBN hanya bisa dilakukan saat status buku berada di ISBN SUBMITTED.');
        }

        $request->validate([
            'isbn' => 'required',
            'tanggal' => 'required|date'
        ]);

        $verification = $perpusnas->verify(
            (string) $request->isbn,
            (string) $book->judul,
            $book->tahun_terbit ? (string) $book->tahun_terbit : null
        );

        if (!$verification['verified']) {
            return back()->with(
                'warning',
                'Validasi ISBN API gagal: ' . ($verification['message'] ?? 'ISBN tidak ditemukan.')
            );
        }

        $book->update([
            'isbn' => $request->isbn,
            'tanggal_isbn_terbit' => $request->tanggal,
            'workflow_status' => 'isbn_approved'
        ]);

        $activity->log(
            $book,
            'ISBN API Verified',
            (string) ($verification['message'] ?? 'ISBN terverifikasi di API Perpusnas')
        );

        $book->update([
            'workflow_status' => 'selesai',
        ]);

        $completion = app(BookCompletionOrchestratorService::class);
        $completion->handle($book, 'isbn_api_verified');

        $activity->log(
            $book,
            'Produksi Selesai Otomatis',
            'Workflow diubah ke selesai setelah ISBN tervalidasi API Perpusnas.'
        );

        return back()->with(
            'success',
            'ISBN berhasil diverifikasi API dan buku otomatis ditandai selesai.'
        );
    }

    public function finishBook(
        Book $book,
        BookActivityService $activity
    ) {
        $book->update([
            'workflow_status' => 'selesai'
        ]);

        $completion = app(BookCompletionOrchestratorService::class);
        $completion->handle($book, 'production_finished');

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

        if (!$book->hasPaidInitialPackageInvoice()) {
            return back()->with('warning', $book->dpPaymentWarningMessage());
        }

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
