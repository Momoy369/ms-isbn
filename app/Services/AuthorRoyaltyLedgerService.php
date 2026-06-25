<?php

namespace App\Services;

use App\Models\AuthorRoyaltyLedger;
use App\Models\AuthorRoyaltyPayoutRequest;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AuthorRoyaltyLedgerService
{
    public function syncForAuthor(User $author): Collection
    {
        $books = Book::with(['externalSales'])
            ->where('author_user_id', $author->id)
            ->where('royalty_enabled', true)
            ->get();

        $ledgers = collect();

        foreach ($books as $book) {
            $groupedSales = $book->externalSales->groupBy(function ($sale) {
                return Carbon::parse($sale->sold_at)->format('Y-m');
            });

            foreach ($groupedSales as $periodKey => $sales) {
                [$year, $month] = explode('-', $periodKey);
                $periodStart = Carbon::create((int) $year, (int) $month, 1)->startOfMonth()->toDateString();
                $periodEnd = Carbon::create((int) $year, (int) $month, 1)->endOfMonth()->toDateString();

                $grossAmount = (float) $sales->sum(function ($sale): float {
                    $gross = (float) ($sale->gross_amount ?? 0);

                    if ($gross > 0) {
                        return $gross;
                    }

                    return (float) $sale->quantity * (float) $sale->unit_price;
                });

                $rate = $book->royaltyRate();
                $royaltyAmount = $grossAmount * $rate;

                $ledger = AuthorRoyaltyLedger::updateOrCreate(
                    [
                        'author_user_id' => $author->id,
                        'book_id' => $book->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ],
                    [
                        'gross_amount' => $grossAmount,
                        'royalty_rate' => $rate,
                        'royalty_amount' => $royaltyAmount,
                        'status' => 'accrued',
                        'generated_at' => now(),
                    ]
                );

                $ledgers->push($ledger);
            }
        }

        return $ledgers->sortByDesc('period_end')->values();
    }

    public function availableAmount(User $author): float
    {
        return (float) $this->syncForAuthor($author)
            ->where('status', 'accrued')
            ->sum('royalty_amount');
    }

    public function allocateToPayoutRequest(AuthorRoyaltyPayoutRequest $request): void
    {
        $ledgers = AuthorRoyaltyLedger::where('author_user_id', $request->author_user_id)
            ->where('status', 'accrued')
            ->orderBy('period_start')
            ->orderBy('id')
            ->get();

        $remaining = (float) $request->amount;

        foreach ($ledgers as $ledger) {
            if ($remaining <= 0) {
                break;
            }

            if ($ledger->royalty_amount <= $remaining + 0.00001) {
                $ledger->update([
                    'status' => 'requested',
                    'payout_request_id' => $request->id,
                ]);

                $remaining -= (float) $ledger->royalty_amount;
            }
        }
    }

    public function markRequestPaid(AuthorRoyaltyPayoutRequest $request): void
    {
        AuthorRoyaltyLedger::where('payout_request_id', $request->id)->update([
            'status' => 'paid',
        ]);
    }
}
