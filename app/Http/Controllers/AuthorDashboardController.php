<?php

namespace App\Http\Controllers;

use App\Models\Book;

class AuthorDashboardController
{
    public function index()
    {
        $user = auth()->user();

        $books = Book::with([
            'activeFiles',
            'activities'
        ])
            ->where(
                'author_user_id',
                auth()->id()
            )
            ->get();

        return view(
            'author.dashboard',
            compact('books')
        );
    }
}