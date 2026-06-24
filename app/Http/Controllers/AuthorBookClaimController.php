<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookClaimRequest;
use Illuminate\Http\Request;

class AuthorBookClaimController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $claimableBooks = collect();

        if (!empty($user->ktp_number)) {
            $claimableBooks = Book::query()
                ->whereNull('author_user_id')
                ->where('author_ktp_number', $user->ktp_number)
                ->latest()
                ->get();
        }

        $myClaimRequests = BookClaimRequest::with('book')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('author.claims.index', compact('claimableBooks', 'myClaimRequests', 'user'));
    }

    public function store(Book $book, Request $request)
    {
        $user = auth()->user();

        if (!$user->isAuthorProfileComplete()) {
            return back()->with('warning', 'Lengkapi profil penulis (KTP, nama KTP, telepon, alamat, tanggal lahir) sebelum klaim buku.');
        }

        if (!empty($book->author_user_id)) {
            return back()->with('warning', 'Buku ini sudah dimiliki akun penulis lain.');
        }

        if (empty($book->author_ktp_number)) {
            return back()->with('warning', 'Buku ini belum memiliki data KTP penulis dari admin, klaim belum bisa diajukan.');
        }

        if ($book->author_ktp_number !== $user->ktp_number) {
            return back()->with('warning', 'KTP akun Anda tidak cocok dengan data KTP pada naskah ini.');
        }

        $existing = BookClaimRequest::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->status === 'pending') {
            return back()->with('warning', 'Klaim untuk buku ini sudah diajukan dan menunggu review admin.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        BookClaimRequest::updateOrCreate(
            [
                'book_id' => $book->id,
                'user_id' => $user->id,
            ],
            [
                'ktp_number' => $user->ktp_number,
                'author_name' => $user->ktp_name ?: $user->name,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'admin_notes' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        return back()->with('success', 'Klaim buku berhasil diajukan. Admin akan memverifikasi data KTP Anda.');
    }
}
