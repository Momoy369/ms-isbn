<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\AuthorInvoice;
use App\Models\Book;
use Illuminate\Http\Request;

class FinanceInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = AuthorInvoice::with(['book', 'user', 'verifier'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            if ($request->type === 'package_billing') {
                $query->where('is_package_billing', true);
            } else {
                $query->where('type', $request->type);
            }
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->q);
            $query->where(function ($inner) use ($keyword) {
                $inner->where('invoice_number', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('book', fn($bookQ) => $bookQ->where('judul', 'like', "%{$keyword}%"))
                    ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$keyword}%"));
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        $stats = [
            'pending' => AuthorInvoice::where('status', 'pending')->sum('amount'),
            'paid' => AuthorInvoice::where('status', 'paid')->sum('amount'),
            'pending_package' => AuthorInvoice::where('status', 'pending')->where('is_package_billing', true)->sum('amount'),
        ];

        $completedBooks = Book::with(['publishingPackage', 'authorInvoices', 'author'])
            ->where('workflow_status', 'selesai')
            ->whereNotNull('author_user_id')
            ->latest()
            ->limit(50)
            ->get();

        return view('finance.invoices.index', compact('invoices', 'stats', 'completedBooks'));
    }

    public function createFinalInvoice(Book $book)
    {
        $book->load('publishingPackage');

        if (!$book->publishing_package_id) {
            return back()->with('warning', 'Buku ini belum memiliki paket penerbitan.');
        }

        if (!$book->author_user_id) {
            return back()->with('warning', 'Buku ini belum memiliki akun author terhubung.');
        }

        $invoice = AuthorInvoice::createFinalPackageInvoice($book);

        if (!$invoice) {
            return back()->with('info', 'Invoice pelunasan sudah ada atau tidak dapat dibuat.');
        }

        return back()->with('success', 'Invoice pelunasan berhasil dibuat: #' . $invoice->invoice_number);
    }

    public function markPaid(Request $request, AuthorInvoice $invoice)
    {
        $data = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice ini sudah berstatus lunas.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $invoice->payment_method ?: 'manual',
            'payment_reference' => $data['payment_reference'] ?? $invoice->payment_reference,
            'verified_by_user_id' => auth()->id(),
            'notes' => $data['notes'] ?? $invoice->notes,
        ]);

        AuthorBookOrder::where('author_invoice_id', $invoice->id)->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Invoice #' . $invoice->invoice_number . ' berhasil ditandai lunas.');
    }

    public function markPending(AuthorInvoice $invoice)
    {
        $invoice->update([
            'status' => 'pending',
            'paid_at' => null,
            'verified_by_user_id' => null,
        ]);

        AuthorBookOrder::where('author_invoice_id', $invoice->id)->update([
            'status' => 'invoiced',
            'paid_at' => null,
        ]);

        return back()->with('success', 'Invoice #' . $invoice->invoice_number . ' dikembalikan ke status pending.');
    }

    public function updateBookLinks(Request $request, Book $book)
    {
        $data = $request->validate([
            'final_drive_link' => ['nullable', 'url', 'max:2000'],
            'final_ebook_link' => ['nullable', 'url', 'max:2000'],
            'links_unlocked_manually' => ['nullable', 'boolean'],
        ]);

        $manualUnlock = (bool) ($request->boolean('links_unlocked_manually'));

        $book->update([
            'final_drive_link' => $data['final_drive_link'] ?? null,
            'final_ebook_link' => $data['final_ebook_link'] ?? null,
            'links_unlocked_manually' => $manualUnlock,
            'links_unlocked_at' => $manualUnlock ? now() : null,
            'links_unlocked_by_user_id' => $manualUnlock ? auth()->id() : null,
        ]);

        return back()->with('success', 'Pengaturan link distribusi berhasil diperbarui.');
    }
}
