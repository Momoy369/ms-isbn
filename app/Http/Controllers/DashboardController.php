<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\BookAssignment;
use App\Models\Book;
use Illuminate\Support\Collection;

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

        $insightBooks = Book::query()
            ->select([
                'id',
                'judul',
                'nomor_naskah',
                'jumlah_halaman',
                'manuscript_a4_pages',
                'manuscript_a5_pages',
                'package_extra_fee',
                'publishing_package_id',
            ])
            ->with('publishingPackage:id,supports_print')
            ->get();

        $withA4 = $insightBooks->filter(function (Book $book): bool {
            return $this->effectiveA4Pages($book) > 0;
        });

        $trackedBooksCount = $withA4->count();
        $sumA4Pages = (int) $withA4->sum(function (Book $book): int {
            return $this->effectiveA4Pages($book);
        });

        $topBooks = $withA4
            ->sortByDesc(function (Book $book): int {
                return $this->effectiveA4Pages($book);
            })
            ->take(5)
            ->map(function (Book $book) {
                $book->effective_a4_pages = $this->effectiveA4Pages($book);

                if (!is_numeric($book->manuscript_a5_pages) || (int) $book->manuscript_a5_pages <= 0) {
                    $book->effective_a5_pages = (int) round($book->effective_a4_pages * 0.8);
                } else {
                    $book->effective_a5_pages = (int) $book->manuscript_a5_pages;
                }

                return $book;
            })
            ->values();

        $manuscriptInsights = [
            'tracked_books' => $trackedBooksCount,
            'unknown_books' => (int) $insightBooks->filter(function (Book $book): bool {
                return $this->effectiveA4Pages($book) <= 0;
            })->count(),
            'sum_a4_pages' => $sumA4Pages,
            'avg_a4_pages' => $trackedBooksCount > 0 ? round($sumA4Pages / $trackedBooksCount, 1) : 0,
            'max_a4_pages' => $trackedBooksCount > 0
                ? (int) $withA4->max(function (Book $book): int {
                    return $this->effectiveA4Pages($book);
                })
                : 0,
            'over_125_a4' => (int) $withA4->filter(function (Book $book): bool {
                return $this->effectiveA4Pages($book) > 125;
            })->count(),
            'over_100_a5_print' => (int) $withA4->filter(function (Book $book): bool {
                $effectiveA5 = (int) (is_numeric($book->manuscript_a5_pages) ? $book->manuscript_a5_pages : 0);
                if ($effectiveA5 <= 0) {
                    $effectiveA5 = (int) round($this->effectiveA4Pages($book) * 0.8);
                }

                return $effectiveA5 > 100
                    && (bool) optional($book->publishingPackage)->supports_print;
            })->count(),
            'top_books' => $topBooks,
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

    private function effectiveA4Pages(Book $book): int
    {
        $a4 = is_numeric($book->manuscript_a4_pages) ? (int) $book->manuscript_a4_pages : 0;
        if ($a4 > 0) {
            return $a4;
        }

        $fallback = is_numeric($book->jumlah_halaman) ? (int) $book->jumlah_halaman : 0;

        return max(0, $fallback);
    }
}