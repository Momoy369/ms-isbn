<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthorBookOrder;
use App\Models\Book;
use App\Models\BookAssignment;

class ProductionDashboardController extends Controller
{
    public function index(Request $request)
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

        $operationsFilters = [
            'channel' => in_array($request->string('op_channel')->toString(), ['all', 'print', 'ebook'], true)
                ? $request->string('op_channel')->toString()
                : 'all',
            'status' => $request->string('op_status')->toString() ?: 'all',
            'adaptation' => in_array($request->string('op_adaptation')->toString(), ['all', 'yes', 'no'], true)
                ? $request->string('op_adaptation')->toString()
                : 'all',
            'keyword' => trim($request->string('op_keyword')->toString()),
        ];

        $statusOptions = [
            'all' => 'Semua Status',
            'paid' => 'Menunggu Proses',
            'revision_requested' => 'Revisi Diminta (Print)',
            'ebook_revision_requested' => 'Revisi Diminta (Ebook)',
            'printing' => 'Sedang Dicetak',
            'processing' => 'Sedang Diproses',
            'ebook_publishing' => 'Sedang Dipublikasikan',
            'print_completed' => 'Selesai Cetak',
            'ebook_completed' => 'Selesai Ebook',
            'shipping' => 'Sedang Dikirim',
            'shipped' => 'Terkirim',
        ];

        $applyOrderFilters = function ($query) use ($operationsFilters) {
            if ($operationsFilters['status'] !== 'all') {
                $query->where('status', $operationsFilters['status']);
            }

            if ($operationsFilters['adaptation'] === 'yes') {
                $query->where('notes', 'like', '%AUTO_PRINT_ADAPTATION_REQUIRED%');
            }

            if ($operationsFilters['adaptation'] === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'not like', '%AUTO_PRINT_ADAPTATION_REQUIRED%');
                });
            }

            if ($operationsFilters['keyword'] !== '') {
                $keyword = $operationsFilters['keyword'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhereHas('book', function ($bookQuery) use ($keyword) {
                            $bookQuery->where('judul', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            }
        };

        $showPrintQueue = in_array($operationsFilters['channel'], ['all', 'print'], true);
        $showEbookQueue = in_array($operationsFilters['channel'], ['all', 'ebook'], true);

        $printQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->whereIn('status', ['paid', 'revision_requested', 'printing', 'processing', 'print_completed', 'shipping', 'shipped']);

        $ebookQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'ebook_publication')
            ->whereIn('status', ['paid', 'ebook_revision_requested', 'ebook_publishing', 'ebook_completed']);

        if ($showPrintQueue) {
            $applyOrderFilters($printQueueQuery);
        }

        if ($showEbookQueue) {
            $applyOrderFilters($ebookQueueQuery);
        }

        $printQueueCount = $showPrintQueue ? (clone $printQueueQuery)->count() : 0;
        $ebookQueueCount = $showEbookQueue ? (clone $ebookQueueQuery)->count() : 0;

        $printQueue = $showPrintQueue
            ? (clone $printQueueQuery)->latest()->limit(10)->get()
            : collect();

        $ebookQueue = $showEbookQueue
            ? (clone $ebookQueueQuery)->latest()->limit(10)->get()
            : collect();

        $revisionQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->whereIn('status', ['revision_requested', 'ebook_revision_requested']);

        if ($operationsFilters['channel'] === 'print') {
            $revisionQueueQuery->where('order_type', 'reprint');
        }

        if ($operationsFilters['channel'] === 'ebook') {
            $revisionQueueQuery->where('order_type', 'ebook_publication');
        }

        $applyOrderFilters($revisionQueueQuery);

        $adaptationQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->where('notes', 'like', '%AUTO_PRINT_ADAPTATION_REQUIRED%');

        if ($showPrintQueue) {
            if ($operationsFilters['status'] !== 'all') {
                $adaptationQueueQuery->where('status', $operationsFilters['status']);
            }

            if ($operationsFilters['keyword'] !== '') {
                $keyword = $operationsFilters['keyword'];

                $adaptationQueueQuery->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhereHas('book', function ($bookQuery) use ($keyword) {
                            $bookQuery->where('judul', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            }
        }

        $revisionQueueCount = (clone $revisionQueueQuery)->count();
        $adaptationQueueCount = $showPrintQueue ? (clone $adaptationQueueQuery)->count() : 0;

        $revisionQueue = $revisionQueueQuery
            ->latest()
            ->limit(10)
            ->get();

        $adaptationQueue = $showPrintQueue
            ? $adaptationQueueQuery->latest()->limit(10)->get()
            : collect();

        $operationsSummary = [
            'print_queue' => $printQueueCount,
            'ebook_queue' => $ebookQueueCount,
            'revision_queue' => $revisionQueueCount,
            'adaptation_queue' => $adaptationQueueCount,
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
                'operationsSummary',
                'operationsFilters',
                'statusOptions'
            )
        );
    }
}