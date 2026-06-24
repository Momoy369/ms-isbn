<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class BookMessageController extends Controller
{
    public function store(
        Book $book,
        Request $request,
        NotificationService $notification
    ) {

        $request->validate([

            'message' =>
                'required|min:3'

        ]);

        $path = null;

        if (
            $request->hasFile(
                'attachment'
            )
        ) {
            $path =
                $request
                    ->file(
                        'attachment'
                    )
                    ->store(
                        'messages',
                        'public'
                    );
        }

        $book->messages()->create([

            'user_id' =>
                auth()->id(),

            'sender_name' =>
                auth()->user()->name,

            'sender_role' =>
                auth()->user()->role,

            'message' =>
                $request->message,

            'attachment' =>
                $path

        ]);

        $notification
            ->sendToBookTeam(

                $book,

                'Pesan Produksi',

                auth()->user()->name .
                ' mengirim pesan pada buku "' .
                $book->judul .
                '"',

                auth()->id()

            );

        app(
            \App\Services\BookActivityService::class
        )->log(

                $book,

                'Pesan Produksi',

                auth()->user()->name .
                ' mengirim pesan'

            );

        return back();
    }
}