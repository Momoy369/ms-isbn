<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use App\Models\Book;
use App\Models\BookReview;

class AuthorDashboardController
{
    public function index()
    {
        $userId = auth()->id();

        $books = Book::with([
            'activeFiles',
            'activities' => fn($q) => $q->latest()->limit(5),
            'assignments',
            'reviews',
            'publishingPackage.items',
            'packageItems',
            'authorInvoices',
            'externalSales',
            'files' => fn($q) => $q->where('is_active', true),
        ])
            ->where('author_user_id', $userId)
            ->latest()
            ->get();

        // ── Statistik global ────────────────────────────────────────────
        $stats = [
            'total' => $books->count(),
            'in_production' => $books->whereNotIn('workflow_status', ['selesai', 'isbn_approved'])->count(),
            'awaiting_review' => $books->whereIn('workflow_status', ['editing_review', 'layout_review', 'cover_review'])->count(),
            'completed' => $books->whereIn('workflow_status', ['isbn_approved', 'selesai'])->count(),
            'with_isbn' => $books->whereNotNull('isbn')->count(),
        ];

        // ── Invoice ──────────────────────────────────────────────────────
        $invoices = AuthorInvoice::with('book')
            ->forAuthor($userId)
            ->latest()
            ->get();

        $invoiceStats = [
            'total_pending' => $invoices->where('status', 'pending')->sum('amount'),
            'total_paid' => $invoices->where('status', 'paid')->sum('amount'),
            'count_pending' => $invoices->where('status', 'pending')->count(),
            'count_paid' => $invoices->where('status', 'paid')->count(),
        ];

        $royaltyData = $books
            ->filter(fn(Book $book) => $book->isRoyaltyEligible())
            ->map(function (Book $book): array {
                $printQty = (int) ($book->jumlah_cetak ?? 0);
                $bookPrice = $book->effectiveSellingPrice();
                $actualQty = (int) $book->externalSales->sum('quantity');
                $rate = $book->royaltyRate();

                if ($actualQty > 0) {
                    $grossFromActualSellingPrice = $actualQty * $bookPrice;
                    $estimated = $grossFromActualSellingPrice * $rate;

                    return [
                        'book_id' => $book->id,
                        'judul' => $book->judul,
                        'print_qty' => $actualQty,
                        'book_price' => $bookPrice,
                        'estimated_royalty' => $estimated,
                        'royalty_rate' => $rate,
                        'is_complete' => true,
                        'royalty_status' => 'actual',
                    ];
                }

                $estimated = $printQty * $bookPrice * $rate;
                $isComplete = in_array($book->workflow_status, ['isbn_approved', 'selesai']);

                return [
                    'book_id' => $book->id,
                    'judul' => $book->judul,
                    'print_qty' => $printQty,
                    'book_price' => $bookPrice,
                    'estimated_royalty' => $estimated,
                    'royalty_rate' => $rate,
                    'is_complete' => $isComplete,
                    'royalty_status' => $isComplete && $printQty > 0 ? 'estimated' : 'pending',
                ];
            })->values();

        // ── Revisi per tahap per buku ────────────────────────────────────
        $revisionCounts = BookReview::where('status', 'revision')
            ->whereIn('book_id', $books->pluck('id'))
            ->selectRaw('book_id, stage, count(*) as total')
            ->groupBy('book_id', 'stage')
            ->get()
            ->groupBy('book_id');

        return view('author.dashboard', compact(
            'books',
            'stats',
            'invoices',
            'invoiceStats',
            'royaltyData',
            'revisionCounts'
        ));
    }
}