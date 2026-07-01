<?php

namespace App\Http\Controllers;

use App\Models\BookAssignment;
use App\Services\BookActivityService;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments =
            BookAssignment::with(
                'book.publishingPackage'
            )
                ->where(function ($query) {
                    $query->where('role', '!=', 'editor')
                        ->orWhereHas('book', function ($bookQuery) {
                            $bookQuery->whereHas('publishingPackage', function ($packageQuery) {
                                $packageQuery->where('includes_editing', true);
                            })->orWhereNull('publishing_package_id');
                        });
                })
                ->latest()
                ->get();

        return view(

            'assignments.index',

            compact(
                'assignments'
            )

        );
    }

    public function complete(
        BookAssignment $assignment,
        BookActivityService $activity
    ) {

        $book = $assignment->book;

        if ($assignment->role === 'editor') {

            $exists =
                $book->files()
                    ->where(
                        'type',
                        'edited_manuscript'
                    )
                    ->exists();

            if (!$exists) {

                return back()->with(
                    'error',
                    'Upload hasil editing terlebih dahulu'
                );

            }

        }

        if ($assignment->role === 'layouter') {

            $exists =
                $book->files()
                    ->where(
                        'type',
                        'layout_pdf'
                    )
                    ->exists();

            if (!$exists) {

                return back()->with(
                    'error',
                    'Upload PDF layout terlebih dahulu'
                );

            }

        }

        if ($assignment->role === 'designer') {

            $exists =
                $book->files()
                    ->where(
                        'type',
                        'cover_final'
                    )
                    ->exists();

            if (!$exists) {

                return back()->with(
                    'error',
                    'Upload cover final terlebih dahulu'
                );

            }

        }

        $assignment->update([

            'completed_at' => now()

        ]);

        $book->assignmentHistories()
            ->create([

                'role' =>
                    $assignment->role,

                'activity' =>
                    'completed',

                'new_person' =>
                    $assignment->person_name

            ]);

        $activity->log(

            $book,

            'Assignment Selesai',

            ucfirst(
                $assignment->role
            ) .

            ' - ' .

            $assignment->person_name

        );

        /*
        |--------------------------------------------------------------------------
        | Workflow Automation
        |--------------------------------------------------------------------------
        */

        if (
            $assignment->role === 'editor'
            &&
            $book->workflow_status === 'editing'
        ) {
            $book->update([

                'workflow_status' =>
                    'editing_review',

                'tanggal_selesai_editing' =>
                    now()

            ]);

            $activity->log(

                $book,

                'Workflow',

                'Menunggu Review Editing Penulis'

            );

        }

        if (
            $assignment->role === 'layouter'
            &&
            $book->workflow_status === 'layout'
        ) {
            $book->update([

                'workflow_status' =>
                    'layout_review',

                'tanggal_selesai_layout' =>
                    now()

            ]);

            $activity->log(

                $book,

                'Workflow',

                'Menunggu Review Layout Penulis'

            );
        }

        if (
            $assignment->role === 'designer'
            &&
            $book->workflow_status === 'cover_design'
        ) {
            $book->update([

                'workflow_status' =>
                    'cover_review',

                'tanggal_selesai_cover' =>
                    now()

            ]);

            $activity->log(

                $book,

                'Workflow',

                'Menunggu Review Cover Penulis'

            );
        }

        return back()->with(

            'success',

            'Assignment selesai'

        );
    }

    public function myAssignments()
    {
        $userId = auth()->id();

        $baseQuery = BookAssignment::query()
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('role', '!=', 'editor')
                    ->orWhereHas('book', function ($bookQuery) {
                        $bookQuery->whereHas('publishingPackage', function ($packageQuery) {
                            $packageQuery->where('includes_editing', true);
                        })->orWhereNull('publishing_package_id');
                    });
            });

        $assignments = (clone $baseQuery)
            ->with('book.publishingPackage')
            ->latest()
            ->get();

        $activeAssignments = (clone $baseQuery)
            ->whereNull('completed_at')
            ->count();

        $overdueAssignments = (clone $baseQuery)
            ->whereNull('completed_at')
            ->where('deadline_at', '<', now())
            ->count();

        $completedThisMonth = (clone $baseQuery)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        return view(

            'assignments.my',

            compact(

                'assignments',

                'activeAssignments',

                'overdueAssignments',

                'completedThisMonth'

            )

        );
    }
}