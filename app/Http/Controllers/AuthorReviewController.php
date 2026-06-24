<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use App\Models\Book;
use App\Models\BookReview;
use App\Services\BookActivityService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AuthorReviewController extends Controller
{
    public function approve(
        Book $book,
        Request $request,
        BookActivityService $activity,
        NotificationService $notification
    ) {

        $stage =
            $request->stage;

        BookReview::create([

            'book_id' =>
                $book->id,

            'user_id' =>
                auth()->id(),

            'stage' =>
                $stage,

            'status' =>
                'approved',

            'note' =>
                $request->note

        ]);

        $targetRole = match ($stage) {

            'editing' => 'editor',

            'layout' => 'layouter',

            'cover' => 'designer',

            default => null
        };

        $assignment = $book->assignments()
            ->where('role', $targetRole)
            ->latest()
            ->first();

        if (
            $assignment &&
            $assignment->user_id
        ) {

            $notification->send(

                $assignment->user_id,

                'Hasil Disetujui',

                'Penulis menyetujui tahap ' .
                strtoupper($stage) .
                ' pada buku "' .
                $book->judul .
                '"',

                $book->id

            );
        }

        switch ($stage) {

            case 'editing':

                $book->update([
                    'workflow_status' => 'layout',
                    'tanggal_mulai_layout' => now()
                ]);

                break;

            case 'layout':

                $book->update([
                    'workflow_status' => 'cover_design',
                    'tanggal_mulai_cover' => now()
                ]);

                break;

            case 'cover':

                $book->update([
                    'workflow_status' => 'audit_isbn'
                ]);

                break;
        }

        $activity->log(

            $book,

            'ACC Penulis',

            auth()->user()->name .
            ' menyetujui tahap ' .
            strtoupper($stage)

        );

        return back();
    }

    public function revision(
        Book $book,
        Request $request,
        BookActivityService $activity,
        NotificationService $notification,
    ) {

        $request->validate([

            'note' => 'required|min:5',

            'attachment' =>
                'nullable|file|max:10240'

        ]);

        $path = null;

        if (
            $request->hasFile(
                'attachment'
            )
        ) {

            $path = $request
                ->file('attachment')
                ->store(
                    'reviews',
                    'public'
                );
        }

        BookReview::create([

            'book_id' =>
                $book->id,

            'user_id' =>
                auth()->id(),

            'stage' =>
                $request->stage,

            'status' =>
                'revision',

            'note' =>
                $request->note,

            'attachment' =>
                $path

        ]);

        $targetRole = match ($request->stage) {

            'editing' => 'editor',

            'layout' => 'layouter',

            'cover' => 'designer',

            default => null
        };

        $assignment = $book->assignments()
            ->where('role', $targetRole)
            ->latest()
            ->first();

        if (
            $assignment &&
            $assignment->user_id
        ) {

            $notification->send(

                $assignment->user_id,

                'Revisi Diterima',

                'Penulis meminta revisi tahap ' .
                strtoupper($request->stage) .
                ' pada buku "' .
                $book->judul .
                '"',

                $book->id

            );
        }

        switch ($request->stage) {

            case 'editing':

                $book->update([
                    'workflow_status' => 'editing'
                ]);

                $book->assignments()
                    ->where('role', 'editor')
                    ->update([
                        'completed_at' => null
                    ]);

                break;

            case 'layout':

                $book->update([
                    'workflow_status' => 'layout'
                ]);

                $book->assignments()
                    ->where('role', 'layouter')
                    ->update([
                        'completed_at' => null
                    ]);

                break;

            case 'cover':

                $book->update([
                    'workflow_status' => 'cover_design'
                ]);

                $book->assignments()
                    ->where('role', 'designer')
                    ->update([
                        'completed_at' => null
                    ]);

                break;
        }

        $activity->log(

            $book,

            'Revisi Penulis',

            strtoupper(
                $request->stage
            )

            .

            ' direvisi'

        );

        // Buat invoice revisi berbayar jika ini bukan revisi pertama pada stage ini
        $revisionInvoice = AuthorInvoice::createRevisionInvoiceIfNeeded($book, $request->stage);

        $successMessage = 'Revisi berhasil dikirim.';
        if ($revisionInvoice) {
            $successMessage .= ' Invoice revisi berbayar #' . $revisionInvoice->invoice_number
                . ' (Rp ' . number_format($revisionInvoice->amount, 0, ',', '.') . ') telah diterbitkan.';
        }

        return back()->with('success', $successMessage);
    }
}