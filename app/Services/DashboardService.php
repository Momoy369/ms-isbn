<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookAssignment;
use Carbon\Carbon;

class DashboardService
{
    public function summary()
    {
        return [

            'total_books' =>

                Book::count(),

            'ready_for_isbn' =>

                Book::where(
                    'workflow_status',
                    'ready_for_isbn'
                )->count(),

            'revisi' =>

                Book::where(
                    'workflow_status',
                    'revisi'
                )->count(),

            'draft' =>

                Book::where(
                    'workflow_status',
                    'draft'
                )->count(),

            'active_assignments' =>

                BookAssignment::whereNull(
                    'completed_at'
                )->count(),

            'overdue_assignments' =>

                BookAssignment::all()
                    ->filter(
                        fn($a) =>
                        $a->getSlaStatus()
                        === 'overdue'
                    )
                    ->count(),

            'waiting_approval' =>

                Book::whereHas(
                    'audits',
                    function ($q) {

                        $q->where(
                            'rule',
                            'approval_author'
                        )
                            ->where(
                                'passed',
                                false
                            );

                    }
                )->count(),

            'isbn_submitted' =>

                Book::where(
                    'workflow_status',
                    'isbn_submitted'
                )->count(),

            'isbn_approved' =>

                Book::where(
                    'workflow_status',
                    'isbn_approved'
                )->count(),

            'selesai' =>

                Book::where(
                    'workflow_status',
                    'selesai'
                )->count(),

        ];
    }

    public function averageEditingDays()
    {
        $books = Book::query()

            ->whereNotNull(
                'tanggal_mulai_editing'
            )

            ->whereNotNull(
                'tanggal_mulai_layout'
            )

            ->get();

        if (
            $books->count() == 0
        ) {
            return 0;
        }

        $total = 0;

        foreach (
            $books as $book
        ) {

            $layoutDate =
                Carbon::parse(
                    $book->tanggal_mulai_layout
                );

            $accDate =
                Carbon::parse(
                    $book->tanggal_acc_penulis
                );

            if (
                $accDate->lt(
                    $layoutDate
                )
            ) {
                continue;
            }

            $total +=
                $layoutDate
                    ->diffInDays(
                        $accDate
                    );

        }

        return round(

            $total
            /
            $books->count(),

            1

        );
    }

    public function averageLayoutDays()
    {
        $books = Book::query()

            ->whereNotNull(
                'tanggal_mulai_layout'
            )

            ->whereNotNull(
                'tanggal_acc_penulis'
            )

            ->get();

        if (
            $books->count() == 0
        ) {
            return 0;
        }

        $total = 0;

        foreach (
            $books as $book
        ) {

            $total +=

                Carbon::parse(
                    $book->tanggal_mulai_layout
                )

                    ->diffInDays(

                        $book->tanggal_acc_penulis

                    );

        }

        return round(

            $total
            /
            $books->count(),

            1

        );
    }

    public function topEditors()
    {
        return BookAssignment::query()

            ->selectRaw('
            person_name,
            count(*) as total
        ')

            ->where('role', 'editor')

            ->whereNotNull('completed_at')

            ->groupBy('person_name')

            ->orderByDesc('total')

            ->limit(5)

            ->get();
    }

    public function topLayouters()
    {
        return BookAssignment::query()

            ->selectRaw('
            person_name,
            count(*) as total
        ')

            ->where('role', 'layouter')

            ->whereNotNull('completed_at')

            ->groupBy('person_name')

            ->orderByDesc('total')

            ->limit(5)

            ->get();
    }

    public function editorWorkloads()
    {
        return BookAssignment::query()

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
    }

    public function layouterWorkloads()
    {
        return BookAssignment::query()

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
    }

    public function alerts()
    {
        return [

            'overdue_assignments' =>

                BookAssignment::whereNull(
                    'completed_at'
                )
                    ->where(
                        'deadline_at',
                        '<',
                        now()
                    )
                    ->count(),

            'warning_assignments' =>

                BookAssignment::whereNull(
                    'completed_at'
                )
                    ->whereBetween(
                        'deadline_at',
                        [
                            now(),
                            now()->copy()->addDay()
                        ]
                    )
                    ->count(),

            'ready_isbn' =>

                Book::where(
                    'workflow_status',
                    'ready_for_isbn'
                )
                    ->count(),

            'waiting_author' =>

                Book::where(
                    'workflow_status',
                    'acc_penulis'
                )
                    ->whereDate(
                        'updated_at',
                        '<',
                        now()->subDays(7)
                    )
                    ->count(),

        ];
    }
}