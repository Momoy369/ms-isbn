<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\BookAssignment;

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

            ]
        );
    }
}