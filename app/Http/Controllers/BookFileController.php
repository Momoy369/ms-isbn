<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\Request;
use App\Services\BookActivityService;

class BookFileController extends Controller
{
    public function store(
        Request $request,
        Book $book,
        \App\Services\NotificationService $notification
    ) {
        $request->validate([
            'type' => 'required',
            'file' => 'required|file|max:20480'
        ]);

        // $file = $request->file('file');

        $book->files()
            ->where(
                'type',
                $request->type
            )
            ->update([

                'is_active' => false

            ]);

        $request->validate([
            'type' => 'required',
            'file' => 'required|file|max:20480'
        ]);

        if ($request->type === 'naskah_final') {

            $request->validate([
                'file' => 'required|mimes:docx'
            ]);
        }

        if ($request->type === 'cover') {

            $request->validate([
                'file' => 'required|mimes:jpg,jpeg,png,pdf'
            ]);
        }

        if ($request->type === 'skk') {

            $request->validate([
                'file' => 'required|mimes:jpg,jpeg,png,pdf'
            ]);
        }

        $file = $request->file('file');

        $path = $file->store(
            'books/' . $book->nomor_naskah,
            'public'
        );

        $latestVersion = BookFile::where(
            'book_id',
            $book->id
        )
            ->where(
                'type',
                $request->type
            )
            ->max('version');

        $version =
            $latestVersion
            ? $latestVersion + 1
            : 1;

        BookFile::create([

            'book_id' => $book->id,

            'type' => $request->type,

            'original_name' =>
                $file->getClientOriginalName(),

            'note' =>
                $request->note,

            'sender_role' =>
                auth()->user()->role,

            'file_path' => $path,

            'mime_type' =>
                $file->getMimeType(),

            'file_size' =>
                $file->getSize(),

            'is_active' => true,

            'version' =>
                $version,

        ]);

        $authorId = $book->author_user_id;

        if (
            $request->type === 'edited_manuscript'
        ) {

            $book->update([
                'workflow_status' =>
                    'editing_review'
            ]);

            if ($authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Hasil editing buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        if (
            $request->type === 'layout_pdf'
        ) {

            $book->update([
                'workflow_status' =>
                    'layout_review'
            ]);

            if ($authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Hasil layout buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        if (
            $request->type === 'cover_final'
        ) {

            $book->update([
                'workflow_status' =>
                    'cover_review'
            ]);

            if ($authorId) {

                $notification->send(

                    $authorId,

                    'Dokumen Baru',

                    'Cover buku "' .
                    $book->judul .
                    '" siap direview.',

                    $book->id

                );
            }
        }

        app(
            BookActivityService::class
        )->log(

                $book,

                'Upload File',

                auth()->user()->name .
                ' mengupload ' .
                $request->type

            );

        return back()->with(
            'success',
            'File berhasil diupload'
        );
    }
    public function restore(
        BookFile $file
    ) {
        BookFile::where(
            'book_id',
            $file->book_id
        )
            ->where(
                'type',
                $file->type
            )
            ->update([
                'is_active' => false
            ]);

        $file->update([
            'is_active' => true
        ]);

        return back()
            ->with(
                'success',
                'Versi berhasil dipulihkan'
            );
    }

    public function submitISBN(
        Book $book
    ) {
        $book->update([

            'workflow_status' =>
                'isbn_submitted',

            'tanggal_pengajuan_isbn' =>
                now()

        ]);

        return back()->with(

            'success',

            'Buku berhasil diajukan ke ISBN'

        );
    }
}