<?php

namespace App\Http\Controllers;

use App\Models\AssignmentHistory;

class AssignmentHistoryController
{
    public function index()
    {
        $histories =

            AssignmentHistory::with(
                'book'
            )

                ->latest()

                ->paginate(50);

        return view(
            'assignments.history',
            compact(
                'histories'
            )
        );
    }
}