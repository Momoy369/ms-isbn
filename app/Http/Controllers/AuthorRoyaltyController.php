<?php

namespace App\Http\Controllers;

use App\Models\AuthorRoyaltyPayoutRequest;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorRoyaltyController extends Controller
{
    public function index()
    {
        $books = Book::with(['externalSales'])
            ->where('author_user_id', auth()->id())
            ->where('royalty_enabled', true)
            ->orderBy('judul')
            ->get();

        $rows = $books->map(function (Book $book): array {
            $salesQty = (int) $book->externalSales->sum('quantity');
            $unitPrice = $book->effectiveSellingPrice();
            $gross = $salesQty * $unitPrice;
            $rate = $book->royaltyRate();

            return [
                'book' => $book,
                'sales_qty' => $salesQty,
                'unit_price' => $unitPrice,
                'gross' => $gross,
                'rate' => $rate,
                'royalty' => $gross * $rate,
            ];
        });

        $summary = [
            'books' => $rows->count(),
            'gross' => (float) $rows->sum('gross'),
            'royalty' => (float) $rows->sum('royalty'),
        ];

        $payoutHistory = auth()->user()
            ->royaltyPayoutRequests()
            ->latest('requested_at')
            ->latest('id')
            ->get();

        $pendingPayouts = (float) $payoutHistory->where('status', 'pending')->sum('amount');
        $availablePayout = max(0, (float) $summary['royalty'] - $pendingPayouts);

        return view('author.royalties.index', compact('rows', 'summary', 'payoutHistory', 'availablePayout'));
    }

    public function export(Request $request)
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

        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');

        $fileName = 'royalty-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($books, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Judul', 'Nomor Naskah', 'Qty', 'Harga Acuan', 'Omzet', 'Rate', 'Royalti', 'Tanggal Sales']);

            foreach ($books as $book) {
                /** @var Book $book */
                $sales = $book->externalSales;

                if ($startDate) {
                    $sales = $sales->where('sold_at', '>=', $startDate);
                }

                if ($endDate) {
                    $sales = $sales->where('sold_at', '<=', $endDate);
                }

                $qty = (int) $sales->sum('quantity');
                $gross = (float) $sales->sum('gross_amount');
                $rate = $book->royaltyRate();
                $royalty = $gross * $rate;
                $lastSoldAt = $sales->isNotEmpty()
                    ? optional($sales->sortByDesc('sold_at')->first()->sold_at)->format('Y-m-d')
                    : '-';

                fputcsv($handle, [
                    $book->judul,
                    $book->nomor_naskah,
                    $qty,
                    $book->effectiveSellingPrice(),
                    $gross,
                    $rate,
                    $royalty,
                    $lastSoldAt,
                ]);
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

    public function requestPayout(Request $request)
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:50000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = auth()->user();

        if (!$user->bank_name || !$user->bank_account_number || !$user->bank_account_holder) {
            return back()->with('warning', 'Lengkapi data rekening terlebih dahulu sebelum meminta pencairan.');
        }

        $books = Book::with(['externalSales'])
            ->where('author_user_id', $user->id)
            ->where('royalty_enabled', true)
            ->get();

        $availableRoyalty = (float) $books->sum(function (Book $book): float {
            $sales = (float) $book->externalSales->sum('gross_amount');

            return $sales * $book->royaltyRate();
        });

        $pending = (float) $user->royaltyPayoutRequests()->where('status', 'pending')->sum('amount');
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

        AuthorRoyaltyPayoutRequest::create([
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

        return back()->with('success', 'Permintaan pencairan royalti berhasil dikirim ke admin.');
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
