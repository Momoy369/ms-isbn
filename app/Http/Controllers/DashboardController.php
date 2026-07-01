<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\BookAssignment;
use App\Models\Book;

class DashboardController
{
    public function index(
        DashboardService $service
    ) {
        $summary =
            $service->summary();

        $overdueAssignments =

            BookAssignment::with(
                'book'
            )

                ->whereNull(
                    'completed_at'
                )

                ->where(
                    'deadline_at',
                    '<',
                    now()
                )

                ->orderBy(
                    'deadline_at'
                )

                ->get();

        $warningAssignments =
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

                ->count();

        $nearDeadlineAssignments =

            BookAssignment::with(
                'book'
            )

                ->whereNull(
                    'completed_at'
                )

                ->whereBetween(

                    'deadline_at',

                    [
                        now(),
                        now()->copy()->addDay()
                    ]

                )

                ->get();

        $trackedBooksQuery = Book::query()->whereNotNull('manuscript_a4_pages');
        $trackedBooksCount = (clone $trackedBooksQuery)->count();
        $sumA4Pages = (int) ((clone $trackedBooksQuery)->sum('manuscript_a4_pages') ?? 0);

        $manuscriptInsights = [
            'tracked_books' => $trackedBooksCount,
            'unknown_books' => (int) Book::query()->whereNull('manuscript_a4_pages')->count(),
            'sum_a4_pages' => $sumA4Pages,
            'avg_a4_pages' => $trackedBooksCount > 0 ? round($sumA4Pages / $trackedBooksCount, 1) : 0,
            'max_a4_pages' => (int) ((clone $trackedBooksQuery)->max('manuscript_a4_pages') ?? 0),
            'over_125_a4' => (int) Book::query()->where('manuscript_a4_pages', '>', 125)->count(),
            'over_100_a5_print' => (int) Book::query()
                ->where('manuscript_a5_pages', '>', 100)
                ->whereHas('publishingPackage', function ($q) {
                    $q->where('supports_print', true);
                })
                ->count(),
            'top_books' => Book::query()
                ->select(['id', 'judul', 'nomor_naskah', 'manuscript_a4_pages', 'manuscript_a5_pages', 'package_extra_fee'])
                ->whereNotNull('manuscript_a4_pages')
                ->orderByDesc('manuscript_a4_pages')
                ->limit(5)
                ->get(),
        ];

        return view(
            'dashboard',
            [

                'summary' =>
                    $service
                        ->summary(),

                'avgEditing' =>
                    $service
                        ->averageEditingDays(),

                'avgLayout' =>
                    $service
                        ->averageLayoutDays(),

                'topEditors' =>
                    $service
                        ->topEditors(),

                'topLayouters' =>
                    $service
                        ->topLayouters(),

                'overdueAssignments' =>
                    $overdueAssignments,

                'warningAssignments' =>
                    $warningAssignments,

                'nearDeadlineAssignments' =>
                    $nearDeadlineAssignments,

                'alerts' =>
                    $service->alerts(),

                'manuscriptInsights' =>
                    $manuscriptInsights,

            ]
        );
    }
}