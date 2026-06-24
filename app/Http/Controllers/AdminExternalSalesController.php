<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ExternalSalesRecord;
use Illuminate\Http\Request;

class AdminExternalSalesController extends Controller
{
    public function index()
    {
        $books = Book::whereNotNull('author_user_id')->orderBy('judul')->get(['id', 'judul', 'nomor_naskah', 'selling_price']);

        $records = ExternalSalesRecord::with(['book', 'inputBy'])
            ->latest('sold_at')
            ->latest('id')
            ->paginate(30);

        $allSales = ExternalSalesRecord::with('book:id,selling_price')
            ->get(['book_id', 'quantity', 'unit_price', 'gross_amount']);

        $totalRoyalty = $allSales->sum(function (ExternalSalesRecord $row) {
            $unitPrice = (float) (optional($row->book)->selling_price ?: $row->unit_price);

            return ((int) $row->quantity) * $unitPrice * 0.20;
        });

        $totals = [
            'gross' => (float) $allSales->sum('gross_amount'),
            'royalty' => (float) $totalRoyalty,
        ];

        return view('admin.external-sales.index', compact('books', 'records', 'totals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'channel' => ['required', 'in:amazon,google_play_books,website,marketplace,other'],
            'format' => ['required', 'in:ebook,print'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'gross_amount' => ['nullable', 'numeric', 'min:0'],
            'sold_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $gross = isset($data['gross_amount']) && $data['gross_amount'] !== null
            ? (float) $data['gross_amount']
            : ((int) $data['quantity'] * (float) $data['unit_price']);

        ExternalSalesRecord::create([
            'book_id' => $data['book_id'],
            'input_by_user_id' => auth()->id(),
            'channel' => $data['channel'],
            'format' => $data['format'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'gross_amount' => $gross,
            'sold_at' => $data['sold_at'],
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Data penjualan eksternal berhasil ditambahkan.');
    }

    public function updateBookPrice(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $book = Book::findOrFail($data['book_id']);
        $book->update([
            'selling_price' => $data['selling_price'],
        ]);

        return back()->with('success', 'Harga jual buku berhasil diperbarui.');
    }
}
