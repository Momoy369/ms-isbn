<?php

namespace App\Http\Controllers;

use App\Models\AuthorRoyaltyPayoutRequest;
use App\Services\AuthorRoyaltyLedgerService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminRoyaltyPayoutController extends Controller
{
    public function index()
    {
        $requests = AuthorRoyaltyPayoutRequest::with(['author', 'ledgers.book'])
            ->latest('requested_at')
            ->latest('id')
            ->paginate(20);

        $all = AuthorRoyaltyPayoutRequest::query()->get(['status', 'amount', 'requested_at']);

        $stats = [
            'pending' => (float) $all->where('status', 'pending')->sum('amount'),
            'approved' => (float) $all->where('status', 'approved')->sum('amount'),
            'paid' => (float) $all->where('status', 'paid')->sum('amount'),
            'rejected' => (float) $all->where('status', 'rejected')->sum('amount'),
            'pending_count' => (int) $all->where('status', 'pending')->count(),
            'approved_count' => (int) $all->where('status', 'approved')->count(),
            'paid_count' => (int) $all->where('status', 'paid')->count(),
            'rejected_count' => (int) $all->where('status', 'rejected')->count(),
        ];

        $monthly = $all
            ->groupBy(function ($row) {
                return optional($row->requested_at)->format('Y-m') ?: 'unknown';
            })
            ->map(fn($rows) => (float) $rows->sum('amount'))
            ->sortKeys();

        return view('finance.royalties.index', compact('requests', 'stats', 'monthly'));
    }

    public function approve(Request $input, AuthorRoyaltyPayoutRequest $request, NotificationService $notifications)
    {
        if ($request->status !== 'pending') {
            return back()->with('info', 'Hanya request pending yang bisa di-approve.');
        }

        $data = $input->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->loadMissing('author', 'ledgers.book');

        $request->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);

        if ($request->author) {
            $notifications->send(
                $request->author_user_id,
                'Pencairan Royalti Disetujui',
                'Permintaan pencairan royalti sebesar Rp ' . number_format((float) $request->amount, 0, ',', '.') . ' telah disetujui dan menunggu transfer.',
                null
            );
        }

        return back()->with('success', 'Permintaan pencairan royalti berhasil di-approve.');
    }

    public function pay(Request $input, AuthorRoyaltyPayoutRequest $request, AuthorRoyaltyLedgerService $ledgerService, NotificationService $notifications)
    {
        if ($request->status !== 'approved') {
            return back()->with('warning', 'Request harus berstatus approved sebelum ditandai paid.');
        }

        $data = $input->validate([
            'payment_reference' => ['required', 'string', 'max:120'],
            'transfer_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $proofPath = $request->transfer_proof_path;
        if ($input->hasFile('transfer_proof')) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }

            $proofPath = $input->file('transfer_proof')->store('royalty-transfer-proofs', 'public');
        }

        $request->update([
            'status' => 'paid',
            'processed_by_user_id' => auth()->id(),
            'processed_at' => now(),
            'paid_at' => now(),
            'payment_reference' => $data['payment_reference'],
            'transfer_proof_path' => $proofPath,
        ]);

        $ledgerService->markRequestPaid($request);

        if ($request->author) {
            $notifications->send(
                $request->author_user_id,
                'Pencairan Royalti Dibayarkan',
                'Pencairan royalti sebesar Rp ' . number_format((float) $request->amount, 0, ',', '.') . ' telah dibayarkan. Referensi: ' . $data['payment_reference'],
                null
            );
        }

        return back()->with('success', 'Transfer royalti berhasil dicatat dan request ditandai paid.');
    }

    public function reject(Request $input, AuthorRoyaltyPayoutRequest $request, AuthorRoyaltyLedgerService $ledgerService, NotificationService $notifications)
    {
        if ($request->status === 'paid') {
            return back()->with('warning', 'Pencairan yang sudah dibayar tidak dapat ditolak.');
        }

        $data = $input->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->loadMissing('ledgers');

        $request->update([
            'status' => 'rejected',
            'approved_by_user_id' => null,
            'approved_at' => null,
            'processed_by_user_id' => auth()->id(),
            'processed_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        foreach ($request->ledgers as $ledger) {
            $ledger->update([
                'status' => 'accrued',
                'payout_request_id' => null,
            ]);
        }

        if ($request->author_user_id) {
            $notifications->send(
                $request->author_user_id,
                'Pencairan Royalti Ditolak',
                'Permintaan pencairan royalti Anda ditolak. ' . ($data['rejection_reason'] ? 'Alasan: ' . $data['rejection_reason'] : 'Silakan cek detail dan ajukan ulang jika diperlukan.'),
                null
            );
        }

        return back()->with('success', 'Permintaan pencairan royalti ditolak dan ledger dikembalikan ke status accrued.');
    }
}
