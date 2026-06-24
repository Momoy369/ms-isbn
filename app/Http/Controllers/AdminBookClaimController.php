<?php

namespace App\Http\Controllers;

use App\Models\BookClaimRequest;
use App\Services\BookActivityService;
use Illuminate\Http\Request;

class AdminBookClaimController extends Controller
{
    public function index()
    {
        $claims = BookClaimRequest::with(['book', 'user', 'reviewer'])
            ->latest()
            ->get();

        return view('admin.book-claims.index', compact('claims'));
    }

    public function approve(BookClaimRequest $claim, Request $request, BookActivityService $activity)
    {
        if (!$claim->isPending()) {
            return back()->with('warning', 'Klaim ini sudah diproses sebelumnya.');
        }

        $book = $claim->book;

        if (!$book) {
            return back()->with('warning', 'Data buku pada klaim ini tidak ditemukan.');
        }

        if (!empty($book->author_user_id)) {
            return back()->with('warning', 'Buku sudah terhubung ke akun penulis lain.');
        }

        if (empty($book->author_ktp_number)) {
            return back()->with('warning', 'Buku belum memiliki KTP penulis pada data admin.');
        }

        if ($book->author_ktp_number !== $claim->ktp_number) {
            return back()->with('warning', 'KTP pada klaim tidak sesuai dengan KTP yang tercatat di data buku.');
        }

        $adminNotes = (string) $request->input('admin_notes', '');

        $book->update([
            'author_user_id' => $claim->user_id,
            'claimed_at' => now(),
        ]);

        $claim->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes !== '' ? $adminNotes : null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Tandai klaim pending lain pada buku yang sama sebagai rejected
        BookClaimRequest::where('book_id', $book->id)
            ->where('id', '!=', $claim->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'admin_notes' => 'Ditolak otomatis karena klaim lain sudah disetujui.',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

        $activity->log(
            $book,
            'Claim Buku Disetujui',
            'Klaim penulis disetujui untuk akun: ' . ($claim->user->name ?? '-')
        );

        return back()->with('success', 'Klaim berhasil disetujui dan buku sudah terhubung ke akun penulis.');
    }

    public function reject(BookClaimRequest $claim, Request $request)
    {
        if (!$claim->isPending()) {
            return back()->with('warning', 'Klaim ini sudah diproses sebelumnya.');
        }

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $claim->update([
            'status' => 'rejected',
            'admin_notes' => $data['admin_notes'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Klaim berhasil ditolak.');
    }
}
