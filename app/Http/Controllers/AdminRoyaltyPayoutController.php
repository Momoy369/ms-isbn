<?php

namespace App\Http\Controllers;

use App\Models\AuthorRoyaltyPayoutRequest;
use App\Services\AuthorRoyaltyLedgerService;
use App\Services\NotificationService;

class AdminRoyaltyPayoutController extends Controller
{
    public function index()
    {
        $requests = AuthorRoyaltyPayoutRequest::with(['author', 'ledgers.book'])
            ->latest('requested_at')
            ->latest('id')
            ->paginate(20);

        $stats = [
            'pending' => AuthorRoyaltyPayoutRequest::where('status', 'pending')->sum('amount'),
            'paid' => AuthorRoyaltyPayoutRequest::where('status', 'paid')->sum('amount'),
            'rejected' => AuthorRoyaltyPayoutRequest::where('status', 'rejected')->sum('amount'),
        ];

        return view('finance.royalties.index', compact('requests', 'stats'));
    }

    public function approve(AuthorRoyaltyPayoutRequest $request, AuthorRoyaltyLedgerService $ledgerService, NotificationService $notifications)
    {
        if ($request->status === 'paid') {
            return back()->with('info', 'Pencairan ini sudah diproses.');
        }

        $request->loadMissing('author', 'ledgers.book');

        $request->update([
            'status' => 'paid',
            'processed_at' => now(),
        ]);

        $ledgerService->markRequestPaid($request);

        if ($request->author) {
            $notifications->send(
                $request->author_user_id,
                'Pencairan Royalti Diproses',
                'Permintaan pencairan royalti sebesar Rp ' . number_format((float) $request->amount, 0, ',', '.') . ' telah diproses dan dibayarkan.',
                null
            );
        }

        return back()->with('success', 'Permintaan pencairan royalti berhasil disetujui dan ditandai lunas.');
    }

    public function reject(AuthorRoyaltyPayoutRequest $request, AuthorRoyaltyLedgerService $ledgerService)
    {
        if ($request->status === 'paid') {
            return back()->with('warning', 'Pencairan yang sudah dibayar tidak dapat ditolak.');
        }

        $request->loadMissing('ledgers');

        $request->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        foreach ($request->ledgers as $ledger) {
            $ledger->update([
                'status' => 'accrued',
                'payout_request_id' => null,
            ]);
        }

        return back()->with('success', 'Permintaan pencairan royalti ditolak dan ledger dikembalikan ke status accrued.');
    }
}
