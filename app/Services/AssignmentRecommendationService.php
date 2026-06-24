<?php

namespace App\Services;

use App\Models\BookAssignment;
use App\Models\User;

class AssignmentRecommendationService
{
    public function recommendEditor()
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

            ->orderBy(
                'total'
            )

            ->first();
    }

    public function recommendLayouter()
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

            ->orderBy(
                'total'
            )

            ->first();
    }

    public function leastLoadedEditor()
    {
        return User::where(
            'role',
            'editor'
        )

            ->withCount([

                'assignments as active_jobs' =>
                    function ($q) {

                        $q->whereNull(
                            'completed_at'
                        );

                    }

            ])

            ->orderBy(
                'active_jobs'
            )

            ->first();
    }

    public function leastLoadedLayouter()
    {
        return User::where(
            'role',
            'layouter'
        )

            ->withCount([

                'assignments as active_jobs' =>
                    function ($q) {

                        $q->whereNull(
                            'completed_at'
                        );

                    }

            ])

            ->orderBy(
                'active_jobs'
            )

            ->first();
    }

    public function leastLoadedDesigner()
    {
        return User::where(
            'role',
            'designer'
        )

            ->withCount([

                'assignments as active_jobs' =>
                    function ($q) {

                        $q->whereNull(
                            'completed_at'
                        );

                    }

            ])

            ->orderBy(
                'active_jobs'
            )

            ->first();
    }
}