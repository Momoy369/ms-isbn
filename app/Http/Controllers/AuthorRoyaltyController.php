<?php

namespace App\Http\Controllers;

use App\Models\AuthorRoyaltyPayoutRequest;
use App\Models\Book;
use App\Models\User;
use App\Services\AuthorRoyaltyLedgerService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorRoyaltyController extends Controller
{
    public function index(AuthorRoyaltyLedgerService $ledgerService)
    {
        $books = Book::with(['externalSales'])
            ->where('author_user_id', auth()->id())
            ->where('royalty_enabled', true)
            ->orderBy('judul')
            ->get();

        $ledgers = $ledgerService->syncForAuthor(auth()->user());

        $rows = $books->map(function (Book $book) use ($ledgers): array {
            $bookLedgers = $ledgers->where('book_id', $book->id);

            return [
                'book' => $book,
                'sales_qty' => (int) $book->externalSales->sum('quantity'),
                'unit_price' => $book->effectiveSellingPrice(),
                'gross' => (float) $bookLedgers->sum('gross_amount'),
                'rate' => $book->royaltyRate(),
                'royalty' => (float) $bookLedgers->sum('royalty_amount'),
                'net_royalty' => (float) $bookLedgers->sum('net_royalty_amount'),
            ];
        });

        $summary = [
            'books' => $rows->count(),
            'gross' => (float) $rows->sum('gross'),
            'royalty' => (float) $rows->sum('royalty'),
            'net_royalty' => (float) $rows->sum('net_royalty'),
        ];

        $payoutHistory = auth()->user()
            ->royaltyPayoutRequests()
            ->latest('requested_at')
            ->latest('id')
            ->get();

        $pendingPayouts = (float) $payoutHistory->whereIn('status', ['pending', 'approved'])->sum('amount');
        $availablePayout = max(0, (float) $ledgers->where('status', 'accrued')->sum('net_royalty_amount') - $pendingPayouts);

        return view('author.royalties.index', compact('rows', 'summary', 'payoutHistory', 'availablePayout', 'ledgers'));
    }

    public function export(Request $request, AuthorRoyaltyLedgerService $ledgerService)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $books = Book::with(['externalSales'])
            ->where('author_user_id', auth()->id())
            ->where('royalty_enabled', true)
            ->orderBy('judul')
            ->get();

        $ledgers = $ledgerService->syncForAuthor(auth()->user());

        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');

        $fileName = 'royalty-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($books, $ledgers, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Judul', 'Nomor Naskah', 'Periode', 'Qty', 'Harga Acuan', 'Omzet', 'Rate', 'Royalti Bruto', 'Royalti Bersih']);

            foreach ($books as $book) {
                /** @var Book $book */
                $bookLedgers = $ledgers->where('book_id', $book->id);

                foreach ($bookLedgers as $ledger) {
                    if ($startDate && $ledger->period_end->lt($startDate)) {
                        continue;
                    }

                    if ($endDate && $ledger->period_start->gt($endDate)) {
                        continue;
                    }

                    fputcsv($handle, [
                        $book->judul,
                        $book->nomor_naskah,
                        $ledger->period_start->format('Y-m') . ' s/d ' . $ledger->period_end->format('Y-m'),
                        '-',
                        $book->effectiveSellingPrice(),
                        $ledger->gross_amount,
                        $ledger->royalty_rate,
                        $ledger->royalty_amount,
                        $ledger->net_royalty_amount,
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function updateBank(Request $request)
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:120'],
            'bank_branch' => ['nullable', 'string', 'max:120'],
        ]);

        $user = auth()->user();
        $user->update($data);

        return back()->with('success', 'Data rekening penulis berhasil diperbarui.');
    }

    public function requestPayout(Request $request, AuthorRoyaltyLedgerService $ledgerService, NotificationService $notifications)
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:50000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = auth()->user();

        if (!$user->bank_name || !$user->bank_account_number || !$user->bank_account_holder) {
            return back()->with('warning', 'Lengkapi data rekening terlebih dahulu sebelum meminta pencairan.');
        }

        $ledgerService->syncForAuthor($user);

        $availableRoyalty = (float) $user->royaltyLedgers()->where('status', 'accrued')->sum('net_royalty_amount');
        $pending = (float) $user->royaltyPayoutRequests()->whereIn('status', ['pending', 'approved'])->sum('amount');
        $available = max(0, $availableRoyalty - $pending);

        $amount = (float) ($data['amount'] ?? $available);

        if ($available < 50000) {
            return back()->with('warning', 'Saldo royalti minimal Rp50.000 untuk pencairan.');
        }

        if ($amount < 50000) {
            return back()->with('warning', 'Nominal pencairan minimal Rp50.000.');
        }

        if ($amount > $available) {
            return back()->with('warning', 'Nominal pencairan melebihi saldo royalti yang tersedia.');
        }

        $payoutRequest = AuthorRoyaltyPayoutRequest::create([
            'author_user_id' => $user->id,
            'amount' => $amount,
            'status' => 'pending',
            'bank_name' => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_holder' => $user->bank_account_holder,
            'bank_branch' => $user->bank_branch,
            'requested_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $ledgerService->allocateToPayoutRequest($payoutRequest);

        $financeUsers = User::whereIn('role', ['finance', 'owner', 'superadmin'])
            ->get(['id']);

        foreach ($financeUsers as $financeUser) {
            $notifications->send(
                $financeUser->id,
                'Request Pencairan Royalti Baru',
                'Ada request baru sebesar Rp ' . number_format($amount, 0, ',', '.') . ' dari ' . $user->name . '.',
                null
            );
        }

        return back()->with('success', 'Permintaan pencairan royalti berhasil dikirim ke admin.');
    }

    public function acceptContract(Request $request, Book $book, NotificationService $notifications)
    {
        if ((int) $book->author_user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'acknowledgement' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $book->update([
            'royalty_contract_status' => 'accepted',
            'royalty_contract_accepted_at' => now(),
            'royalty_contract_rejected_at' => null,
            'royalty_contract_acknowledgement' => $data['acknowledgement'],
        ]);

        if ($book->royalty_enabled_by_user_id) {
            $notifications->send(
                $book->royalty_enabled_by_user_id,
                'Kontrak Royalti Disetujui Penulis',
                'Kontrak royalti buku "' . $book->judul . '" telah disetujui penulis.',
                $book->id
            );
        }

        return back()->with('success', 'Kontrak royalti berhasil disetujui.');
    }

    public function rejectContract(Request $request, Book $book, NotificationService $notifications)
    {
        if ((int) $book->author_user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'acknowledgement' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $book->update([
            'royalty_contract_status' => 'rejected',
            'royalty_contract_rejected_at' => now(),
            'royalty_contract_accepted_at' => null,
            'royalty_contract_acknowledgement' => $data['acknowledgement'],
        ]);

        if ($book->royalty_enabled_by_user_id) {
            $notifications->send(
                $book->royalty_enabled_by_user_id,
                'Kontrak Royalti Ditolak Penulis',
                'Kontrak royalti buku "' . $book->judul . '" ditolak penulis. Cek catatan pada halaman royalti.',
                $book->id
            );
        }

        return back()->with('warning', 'Kontrak royalti ditolak. Tim admin akan meninjau kembali.');
    }

    public function downloadDocument(Book $book, string $type)
    {
        if ((int) $book->author_user_id !== (int) auth()->id()) {
            abort(403);
        }

        if (!$book->royalty_enabled) {
            abort(404);
        }

        $path = match ($type) {
            'agreement' => $book->royalty_agreement_file_path,
            'contract' => $book->royalty_contract_file_path,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->download(Storage::disk('public')->path($path));
    }
}
