<?php

namespace App\Http\Controllers;

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

                ->whereNull(
                    'completed_at'
                )

                ->get()

                ->filter(
                    fn($a)
                    =>
                    $a->getWarningLevel()
                    == 'warning'
                )

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
                'layouterWorkloads'
            )
        );
    }
}