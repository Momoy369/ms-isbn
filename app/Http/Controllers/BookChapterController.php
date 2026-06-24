<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookChapterController extends Controller
{
    public function store(
        Book $book,
        Request $request
    ) {
        $request->validate([

            'title' => 'required',

            'content' => 'required'

        ]);

        $lastOrder = $book
            ->chapters()
            ->max('chapter_order');

        $book->chapters()->create([

            'chapter_order' =>
                ($lastOrder ?? 0) + 1,

            'title' =>
                $request->title,

            'content' =>
                $request->content

        ]);

        return back()
            ->with(
                'success',
                'Bab berhasil ditambahkan'
            );
    }
}