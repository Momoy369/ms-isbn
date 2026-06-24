<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPackageItem;
use Illuminate\Http\Request;

class BookPackageItemController extends Controller
{
    public function toggle(BookPackageItem $item, Request $request)
    {
        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_at' => $item->is_completed ? null : now(),
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Status item paket diperbarui');
    }

    public function sync(Book $book)
    {
        $book->syncPackageItems();

        return back()->with('success', 'Item paket berhasil disinkronkan');
    }
}
