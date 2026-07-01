<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\Book;
use App\Models\BookAssignment;

class ProductionDashboardController extends Controller
{
    public function index()
    {
        $totalBooks = Book::count();

        $overdueAssignments =
            BookAssignment::query()
                ->whereNull('completed_at')
                ->whereDate('deadline_at', '<', now())
                ->count();

        $editingOverdue =
            BookAssignment::query()
                ->where('role', 'editor')
                ->whereNull('completed_at')
                ->whereDate('deadline_at', '<', now())
                ->count();

        $layoutOverdue =
            BookAssignment::query()
                ->where('role', 'layouter')
                ->whereNull('completed_at')
                ->whereDate('deadline_at', '<', now())
                ->count();

        $readyIsbn =
            Book::query()
                ->where(
                    'workflow_status',
                    'ready_for_isbn'
                )
                ->count();

        $waitingApproval =
            Book::query()
                ->where(
                    'workflow_status',
                    'acc_penulis'
                )
                ->count();

        $editingQueue =
            BookAssignment::with('book')

                ->where(
                    'role',
                    'editor'
                )

                ->whereNull(
                    'completed_at'
                )

                ->orderBy(
                    'deadline_at'
                )

                ->get();

        $layoutQueue =
            BookAssignment::with('book')

                ->where(
                    'role',
                    'layouter'
                )

                ->whereNull(
                    'completed_at'
                )

                ->orderBy(
                    'deadline_at'
                )

                ->get();

        $coverQueue =
            BookAssignment::with('book')

                ->where(
                    'role',
                    'designer'
                )

                ->whereNull(
                    'completed_at'
                )

                ->orderBy(
                    'deadline_at'
                )

                ->get();

        $productionProgress =

            Book::query()

                ->latest()

                ->limit(20)

                ->get();

        $warningAssignments =
            BookAssignment::query()
                ->whereNull('completed_at')
                ->whereBetween('deadline_at', [now(), now()->copy()->addDay()])
                ->count();

        $editorWorkloads =

            BookAssignment::query()

                ->selectRaw(
                    '
            person_name,
            count(*) as total
            '
                )

                ->where(
                    'role',
                    'editor'
                )

                ->whereNull(
                    'completed_at'
                )

                ->groupBy(
                    'person_name'
                )

                ->orderByDesc(
                    'total'
                )

                ->get();

        $layouterWorkloads =

            BookAssignment::query()

                ->selectRaw(
                    '
            person_name,
            count(*) as total
            '
                )

                ->where(
                    'role',
                    'layouter'
                )

                ->whereNull(
                    'completed_at'
                )

                ->groupBy(
                    'person_name'
                )

                ->orderByDesc(
                    'total'
                )

                ->get();

        $printQueue = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->whereIn('status', ['paid', 'revision_requested', 'printing', 'processing', 'print_completed', 'shipping', 'shipped'])
            ->latest()
            ->limit(10)
            ->get();

        $ebookQueue = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'ebook_publication')
            ->whereIn('status', ['paid', 'ebook_revision_requested', 'ebook_publishing'])
            ->latest()
            ->limit(10)
            ->get();

        $revisionQueue = AuthorBookOrder::with(['book', 'user'])
            ->whereIn('status', ['revision_requested', 'ebook_revision_requested'])
            ->latest()
            ->limit(10)
            ->get();

        $adaptationQueue = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->where('notes', 'like', '%AUTO_PRINT_ADAPTATION_REQUIRED%')
            ->latest()
            ->limit(10)
            ->get();

        $operationsSummary = [
            'print_queue' => $printQueue->count(),
            'ebook_queue' => $ebookQueue->count(),
            'revision_queue' => $revisionQueue->count(),
            'adaptation_queue' => $adaptationQueue->count(),
        ];

        return view(
            'production.dashboard',
            compact(
                'totalBooks',
                'overdueAssignments',
                'editingOverdue',
                'layoutOverdue',
                'readyIsbn',
                'waitingApproval',
                'editingQueue',
                'layoutQueue',
                'coverQueue',
                'productionProgress',
                'warningAssignments',
                'editorWorkloads',
                'layouterWorkloads',
                'printQueue',
                'ebookQueue',
                'revisionQueue',
                'adaptationQueue',
                'operationsSummary'
            )
        );
    }
}