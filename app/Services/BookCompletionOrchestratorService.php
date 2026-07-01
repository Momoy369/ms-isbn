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

        $supportsPrint = (bool) optional($book->publishingPackage)->supports_print;
        $supportsEbook = (bool) optional($book->publishingPackage)->supports_ebook;

        if (!$supportsPrint && !$supportsEbook) {
            // Backward compatible fallback when old package data has no channel configured.
            $supportsPrint = true;
        }

        $order = null;

        if ($supportsPrint) {
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

            app(AuthorBookOrderStatusHistoryService::class)->record(
                $order,
                null,
                (string) $order->status,
                'Order cetak otomatis dibuat dari workflow buku.',
                null,
                'orchestrator'
            );
        }

        if ($supportsEbook) {
            $ebookOrder = AuthorBookOrder::firstOrCreate(
                [
                    'book_id' => $book->id,
                    'user_id' => $book->author_user_id,
                    'order_type' => 'ebook_publication',
                    'author_invoice_id' => $invoice?->id,
                ],
                [
                    'title' => $book->judul,
                    'pages' => (int) ($book->jumlah_halaman ?? 0),
                    'quantity' => 1,
                    'unit_price' => 0,
                    'subtotal' => 0,
                    'shipping_cost' => 0,
                    'total_amount' => 0,
                    'status' => $invoice ? 'invoiced' : 'pending',
                    'notes' => 'AUTO_EBOOK_QUEUE:' . $trigger,
                ]
            );

            app(AuthorBookOrderStatusHistoryService::class)->record(
                $ebookOrder,
                null,
                (string) $ebookOrder->status,
                'Order ebook otomatis dibuat dari workflow buku.',
                null,
                'orchestrator'
            );
        }

        if ($invoice && $invoice->status === 'paid') {
            AuthorBookOrder::where('author_invoice_id', $invoice->id)
                ->whereIn('order_type', ['reprint', 'ebook_publication'])
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

            AuthorBookOrder::where('author_invoice_id', $invoice->id)
                ->whereIn('order_type', ['reprint', 'ebook_publication'])
                ->get()
                ->each(function (AuthorBookOrder $createdOrder) {
                    app(AuthorBookOrderStatusHistoryService::class)->record(
                        $createdOrder,
                        'invoiced',
                        'paid',
                        'Invoice dibayar dan order diteruskan ke workspace terkait.',
                        null,
                        'orchestrator'
                    );
                });
        }

        if ($invoice) {
            app(InvoiceDispatchService::class)->dispatchAuthorInvoice($invoice);
        }

        return ['invoice' => $invoice, 'order' => $order];
    }
}
