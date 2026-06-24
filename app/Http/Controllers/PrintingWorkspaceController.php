<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Services\FinalBookPackageService;
use Illuminate\Http\Request;

class PrintingWorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $query = AuthorBookOrder::with(['book', 'user', 'invoice'])
            ->where('order_type', 'reprint')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->integer('book_id'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($inner) use ($keyword) {
                $inner->where('title', 'like', "%{$keyword}%")
                    ->orWhereHas('book', fn($bookQ) => $bookQ->where('judul', 'like', "%{$keyword}%"))
                    ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$keyword}%"));
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        $stats = [
            'invoiced' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'invoiced')->count(),
            'paid' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'paid')->count(),
            'processing' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'processing')->count(),
            'completed' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'completed')->count(),
        ];

        return view('printing.workspace.index', compact('orders', 'stats'));
    }

    public function updateStatus(Request $request, AuthorBookOrder $order)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:paid,processing,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $order->notes,
        ]);

        return back()->with('success', 'Status order cetak ulang berhasil diperbarui.');
    }

    public function show(AuthorBookOrder $order, FinalBookPackageService $finalPackage)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $order->load(['book.files', 'user', 'invoice']);
        $book = $order->book;

        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $checklist = $finalPackage->checklist($book);

        return view('printing.workspace.show', compact('order', 'book', 'checklist'));
    }

    public function uploadFinalFile(Request $request, AuthorBookOrder $order, FinalBookPackageService $finalPackage)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $book = $order->book;
        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $data = $request->validate([
            'type' => ['required', 'string'],
            'file' => ['required', 'file', 'max:51200'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $finalPackage->validateAndStore(
                $book,
                $data['type'],
                $request->file('file'),
                $data['note'] ?? null,
                (string) (auth()->user()->role ?? 'printing')
            );
        } catch (\Throwable $e) {
            return back()->with('warning', 'Upload final file gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Final file berhasil diupload dan diverifikasi.');
    }
}
