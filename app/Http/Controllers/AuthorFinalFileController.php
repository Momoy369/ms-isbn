<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookFile;
use App\Services\FinalBookPackageService;
use Illuminate\Support\Facades\Storage;

class AuthorFinalFileController extends Controller
{
    public function index(Book $book, FinalBookPackageService $finalPackage)
    {
        $this->authorizeAuthor($book);
        $this->authorizeAccess($book);

        $checklist = $finalPackage->checklist($book);

        return view('author.books.final-files', compact('book', 'checklist'));
    }

    public function download(Book $book, BookFile $file, FinalBookPackageService $finalPackage)
    {
        $this->authorizeAuthor($book);
        $this->authorizeAccess($book);

        if ((int) $file->book_id !== (int) $book->id) {
            abort(404);
        }

        if (!in_array($file->type, $finalPackage->requiredTypes(), true)) {
            abort(403, 'Tipe file ini tidak termasuk paket final author.');
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($file->file_path);

        return response()->download($absolutePath, $file->original_name);
    }

    private function authorizeAuthor(Book $book): void
    {
        if ((int) $book->author_user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function authorizeAccess(Book $book): void
    {
        if (!$book->canAuthorAccessDeliveryLinks()) {
            abort(403, 'Akses file final dibuka setelah invoice paket lunas 100%.');
        }
    }
}
