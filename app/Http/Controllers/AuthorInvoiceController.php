<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthorInvoiceController extends Controller
{
    /**
     * List all invoices belonging to the logged-in author.
     */
    public function index()
    {
        $invoices = AuthorInvoice::with('book')
            ->forAuthor(auth()->id())
            ->latest()
            ->paginate(20);

        $totalPending = AuthorInvoice::forAuthor(auth()->id())
            ->pending()
            ->sum('amount');

        $totalPaid = AuthorInvoice::forAuthor(auth()->id())
            ->paid()
            ->sum('amount');

        return view('author.invoices.index', compact('invoices', 'totalPending', 'totalPaid'));
    }

    /**
     * Show single invoice detail.
     */
    public function show(AuthorInvoice $invoice)
    {
        $this->authorizeAuthor($invoice);

        $invoice->load('book.publishingPackage');

        return view('author.invoices.show', compact('invoice'));
    }

    /**
     * Author uploads payment proof.
     */
    public function uploadProof(Request $request, AuthorInvoice $invoice)
    {
        $this->authorizeAuthor($invoice);

        if (!$invoice->isPending()) {
            return back()->with('warning', 'Invoice ini tidak dapat diperbarui karena sudah ' . $invoice->getStatusLabel() . '.');
        }

        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $invoice->update([
            'payment_proof' => $path,
            'notes' => $data['notes'] ?? $invoice->notes,
            'payment_method' => 'author_upload',
            'status' => 'pending', // Admin yang confirm paid
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Admin akan mengkonfirmasi dalam 1×24 jam.');
    }

    /**
     * Simulasi pembayaran langsung dari panel author.
     */
    public function payNow(AuthorInvoice $invoice)
    {
        $this->authorizeAuthor($invoice);

        if (!$invoice->isPending()) {
            return back()->with('warning', 'Invoice ini sudah ' . $invoice->getStatusLabel() . '.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'author_online',
            'payment_reference' => 'AUTH-' . strtoupper((string) \Illuminate\Support\Str::random(10)),
            'verified_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Pembayaran berhasil. Invoice #' . $invoice->invoice_number . ' sudah lunas.');
    }

    /**
     * Admin confirms payment (admin only – role check in route middleware).
     */
    public function confirmPayment(AuthorInvoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Invoice #' . $invoice->invoice_number . ' ditandai sebagai LUNAS.');
    }

    private function authorizeAuthor(AuthorInvoice $invoice): void
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berwenang mengakses invoice ini.');
        }
    }
}
