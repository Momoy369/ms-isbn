<?php

namespace App\Services;

use App\Models\AuthorBookOrder;
use App\Models\AuthorInvoice;
use App\Models\Book;

class BookCompletionOrchestratorService
{
    public function handle(Book $book, string $trigger): array
    {
        $book->loadMissing(['publishingPackage', 'author']);

        if (!$book->author_user_id) {
            return ['invoice' => null, 'order' => null];
        }

        $invoice = AuthorInvoice::createFinalPackageInvoice($book);

        if (!$invoice) {
            $invoice = AuthorInvoice::where('book_id', $book->id)
                ->where('is_package_billing', true)
                ->where('installment_number', 2)
                ->latest()
                ->first();
        }

        $order = AuthorBookOrder::firstOrCreate(
            [
                'book_id' => $book->id,
                'user_id' => $book->author_user_id,
                'order_type' => 'reprint',
                'author_invoice_id' => $invoice?->id,
            ],
            [
                'title' => $book->judul,
                'pages' => (int) ($book->jumlah_halaman ?? 0),
                'quantity' => max(1, (int) ($book->jumlah_cetak ?? 1)),
                'unit_price' => 0,
                'subtotal' => 0,
                'shipping_cost' => 0,
                'total_amount' => (float) ($invoice?->amount ?? 0),
                'status' => $invoice ? 'invoiced' : 'pending',
                'notes' => 'AUTO_PRINT_QUEUE:' . $trigger,
            ]
        );

        if ($invoice && $invoice->status === 'paid') {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        if ($invoice) {
            app(InvoiceDispatchService::class)->dispatchAuthorInvoice($invoice);
        }

        return ['invoice' => $invoice, 'order' => $order];
    }
}
