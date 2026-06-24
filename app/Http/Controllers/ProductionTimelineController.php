<?php

namespace App\Http\Controllers;

use App\Models\Book;

class ProductionTimelineController extends Controller
{
    public function index()
    {
        $books = Book::with('assignments')
            ->latest()
            ->paginate(20);

        return view(
            'production.timeline',
            compact('books')
        );
    }
}