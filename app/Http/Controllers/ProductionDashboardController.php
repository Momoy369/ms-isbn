<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $operationsFilters = $this->resolveOperationsFilters($request);
        $statusOptions = $this->statusOptions();
        $slaAgeOptions = $this->slaAgeOptions();
        $perPageOptions = [10, 25, 50];

        $showPrintQueue = in_array($operationsFilters['channel'], ['all', 'print'], true);
        $showEbookQueue = in_array($operationsFilters['channel'], ['all', 'ebook'], true);

        $printQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->whereIn('status', ['paid', 'revision_requested', 'printing', 'processing', 'print_completed', 'shipping', 'shipped']);

        $ebookQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'ebook_publication')
            ->whereIn('status', ['paid', 'ebook_revision_requested', 'ebook_publishing', 'ebook_completed']);

        if ($showPrintQueue) {
            $this->applyCommonOrderFilters($printQueueQuery, $operationsFilters);
        }

        if ($showEbookQueue) {
            $this->applyCommonOrderFilters($ebookQueueQuery, $operationsFilters);
        }

        $printQueueCount = $showPrintQueue ? (clone $printQueueQuery)->count() : 0;
        $ebookQueueCount = $showEbookQueue ? (clone $ebookQueueQuery)->count() : 0;

        $printQueue = $showPrintQueue
            ? (clone $printQueueQuery)->latest()->paginate($operationsFilters['per_page'], ['*'], 'print_page')
            : AuthorBookOrder::query()->whereRaw('1 = 0')->paginate($operationsFilters['per_page'], ['*'], 'print_page');

        $ebookQueue = $showEbookQueue
            ? (clone $ebookQueueQuery)->latest()->paginate($operationsFilters['per_page'], ['*'], 'ebook_page')
            : AuthorBookOrder::query()->whereRaw('1 = 0')->paginate($operationsFilters['per_page'], ['*'], 'ebook_page');

        $revisionQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->whereIn('status', ['revision_requested', 'ebook_revision_requested']);

        if ($operationsFilters['channel'] === 'print') {
            $revisionQueueQuery->where('order_type', 'reprint');
        }

        if ($operationsFilters['channel'] === 'ebook') {
            $revisionQueueQuery->where('order_type', 'ebook_publication');
        }

        $this->applyCommonOrderFilters($revisionQueueQuery, $operationsFilters);

        $adaptationQueueQuery = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->where('notes', 'like', '%AUTO_PRINT_ADAPTATION_REQUIRED%');

        if ($showPrintQueue) {
            if ($operationsFilters['status'] !== 'all') {
                $adaptationQueueQuery->where('status', $operationsFilters['status']);
            }

            $this->applyDateAndAgeFilters($adaptationQueueQuery, $operationsFilters);

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
            ->paginate($operationsFilters['per_page'], ['*'], 'revision_page');

        $adaptationQueue = $showPrintQueue
            ? $adaptationQueueQuery->latest()->paginate($operationsFilters['per_page'], ['*'], 'adaptation_page')
            : AuthorBookOrder::query()->whereRaw('1 = 0')->paginate($operationsFilters['per_page'], ['*'], 'adaptation_page');

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
                'statusOptions',
                'slaAgeOptions',
                'perPageOptions'
            )
        );
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $operationsFilters = $this->resolveOperationsFilters($request);

        $exportQuery = AuthorBookOrder::with(['book', 'user'])
            ->whereIn('order_type', ['reprint', 'ebook_publication'])
            ->whereIn('status', [
                'paid',
                'revision_requested',
                'ebook_revision_requested',
                'printing',
                'processing',
                'ebook_publishing',
                'print_completed',
                'ebook_completed',
                'shipping',
                'shipped',
            ]);

        if ($operationsFilters['channel'] === 'print') {
            $exportQuery->where('order_type', 'reprint');
        }

        if ($operationsFilters['channel'] === 'ebook') {
            $exportQuery->where('order_type', 'ebook_publication');
        }

        $this->applyCommonOrderFilters($exportQuery, $operationsFilters);

        $orders = $exportQuery->latest()->get();

        $filename = 'operations-dashboard-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order ID',
                'Order Type',
                'Judul Buku',
                'Author',
                'Status',
                'Platform Ebook',
                'Adaptasi Cetak',
                'Tanggal Dibuat',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->order_type,
                    $order->title ?? optional($order->book)->judul,
                    optional($order->user)->name,
                    $order->status,
                    $order->ebook_platform,
                    str_contains((string) $order->notes, 'AUTO_PRINT_ADAPTATION_REQUIRED') ? 'Ya' : 'Tidak',
                    optional($order->created_at)?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveOperationsFilters(Request $request): array
    {
        $startDateInput = $request->string('op_start_date')->toString();
        $endDateInput = $request->string('op_end_date')->toString();

        $startDate = $this->safeParseDate($startDateInput, true);
        $endDate = $this->safeParseDate($endDateInput, false);

        return [
            'channel' => in_array($request->string('op_channel')->toString(), ['all', 'print', 'ebook'], true)
                ? $request->string('op_channel')->toString()
                : 'all',
            'status' => $request->string('op_status')->toString() ?: 'all',
            'adaptation' => in_array($request->string('op_adaptation')->toString(), ['all', 'yes', 'no'], true)
                ? $request->string('op_adaptation')->toString()
                : 'all',
            'keyword' => trim($request->string('op_keyword')->toString()),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_date_input' => $startDateInput,
            'end_date_input' => $endDateInput,
            'sla_age' => array_key_exists($request->string('op_sla_age')->toString(), $this->slaAgeOptions())
                ? $request->string('op_sla_age')->toString()
                : 'all',
            'per_page' => in_array((int) $request->integer('op_per_page', 10), [10, 25, 50], true)
                ? (int) $request->integer('op_per_page', 10)
                : 10,
        ];
    }

    private function applyCommonOrderFilters($query, array $operationsFilters): void
    {
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

        $this->applyDateAndAgeFilters($query, $operationsFilters);
    }

    private function applyDateAndAgeFilters($query, array $operationsFilters): void
    {
        if ($operationsFilters['start_date'] !== null) {
            $query->where('created_at', '>=', $operationsFilters['start_date']);
        }

        if ($operationsFilters['end_date'] !== null) {
            $query->where('created_at', '<=', $operationsFilters['end_date']);
        }

        if ($operationsFilters['sla_age'] !== 'all') {
            $now = now();

            if ($operationsFilters['sla_age'] === 'today') {
                $query->whereDate('created_at', $now->toDateString());
            }

            if ($operationsFilters['sla_age'] === 'age_1_3') {
                $query->whereBetween('created_at', [$now->copy()->subDays(3), $now->copy()->subDay()]);
            }

            if ($operationsFilters['sla_age'] === 'age_4_7') {
                $query->whereBetween('created_at', [$now->copy()->subDays(7), $now->copy()->subDays(4)]);
            }

            if ($operationsFilters['sla_age'] === 'age_gt_7') {
                $query->where('created_at', '<', $now->copy()->subDays(7));
            }
        }
    }

    private function statusOptions(): array
    {
        return [
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
    }

    private function slaAgeOptions(): array
    {
        return [
            'all' => 'Semua Umur SLA',
            'today' => 'Hari Ini',
            'age_1_3' => '1-3 Hari',
            'age_4_7' => '4-7 Hari',
            'age_gt_7' => '> 7 Hari',
        ];
    }

    private function safeParseDate(string $value, bool $startOfDay): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            $parsed = Carbon::parse($value);

            return $startOfDay ? $parsed->startOfDay() : $parsed->endOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}