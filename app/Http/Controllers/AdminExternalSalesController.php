<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ExternalSalesRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminExternalSalesController extends Controller
{
    public function index()
    {
        $selectedBookId = (int) request('book_id');

        $books = Book::whereNotNull('author_user_id')
            ->whereIn('workflow_status', ['isbn_approved', 'selesai'])
            ->orderBy('judul')
            ->get([
                'id',
                'judul',
                'nomor_naskah',
                'selling_price',
                'royalty_enabled',
                'royalty_rate',
                'royalty_distribution_online',
                'royalty_distribution_ebook',
                'royalty_distribution_marketplace',
                'royalty_agreement_file_path',
                'royalty_contract_file_path',
                'royalty_notes',
            ]);

        $eligibleBooks = $books->where('royalty_enabled', true)->values();
        $selectedBook = $selectedBookId ? $books->firstWhere('id', $selectedBookId) : null;

        $records = ExternalSalesRecord::with(['book', 'inputBy'])
            ->latest('sold_at')
            ->latest('id')
            ->paginate(30);

        $allSales = ExternalSalesRecord::with('book:id,selling_price,royalty_rate,royalty_enabled')
            ->get(['book_id', 'quantity', 'unit_price', 'gross_amount']);

        $totalRoyalty = $allSales->sum(function (ExternalSalesRecord $row) {
            $unitPrice = (float) (optional($row->book)->selling_price ?: $row->unit_price);
            $rate = optional($row->book)->royaltyRate() ?? 0.20;

            return ((int) $row->quantity) * $unitPrice * $rate;
        });

        $totals = [
            'gross' => (float) $allSales->sum('gross_amount'),
            'royalty' => (float) $totalRoyalty,
        ];

        return view('admin.external-sales.index', compact('books', 'eligibleBooks', 'records', 'totals', 'selectedBook'));
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

        $book = Book::findOrFail($data['book_id']);

        if (!$book->isRoyaltyEligible()) {
            return back()->with('warning', 'Buku ini belum diaktifkan untuk program distribusi royalti.');
        }

        if (!$book->canDistributeByChannel((string) $data['channel'], (string) $data['format'])) {
            return back()->with('warning', 'Channel/format ini belum diizinkan pada konfigurasi distribusi buku tersebut.');
        }

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

    public function updateRoyaltyProgram(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'royalty_enabled' => ['nullable', 'boolean'],
            'royalty_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'royalty_distribution_online' => ['nullable', 'boolean'],
            'royalty_distribution_ebook' => ['nullable', 'boolean'],
            'royalty_distribution_marketplace' => ['nullable', 'boolean'],
            'royalty_notes' => ['nullable', 'string'],
            'royalty_agreement_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'royalty_contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        $enabled = (bool) ($data['royalty_enabled'] ?? false);

        if ($enabled) {
            $hasAnyDistribution = (bool) ($data['royalty_distribution_online'] ?? false)
                || (bool) ($data['royalty_distribution_ebook'] ?? false)
                || (bool) ($data['royalty_distribution_marketplace'] ?? false);

            if (!$hasAnyDistribution) {
                return back()->with('warning', 'Aktifkan minimal satu channel distribusi (online/ebook/marketplace).');
            }

            $hasAgreement = $request->hasFile('royalty_agreement_file') || !empty($book->royalty_agreement_file_path);
            $hasContract = $request->hasFile('royalty_contract_file') || !empty($book->royalty_contract_file_path);

            if (!$hasAgreement || !$hasContract) {
                return back()->with('warning', 'Program royalti aktif membutuhkan upload surat perjanjian dan kontrak terpisah.');
            }
        }

        $update = [
            'royalty_enabled' => $enabled,
            'royalty_rate' => $enabled ? ($data['royalty_rate'] ?? null) : null,
            'royalty_distribution_online' => $enabled ? (bool) ($data['royalty_distribution_online'] ?? false) : false,
            'royalty_distribution_ebook' => $enabled ? (bool) ($data['royalty_distribution_ebook'] ?? false) : false,
            'royalty_distribution_marketplace' => $enabled ? (bool) ($data['royalty_distribution_marketplace'] ?? false) : false,
            'royalty_notes' => $enabled ? ($data['royalty_notes'] ?? null) : null,
            'royalty_enabled_at' => $enabled ? now() : null,
            'royalty_enabled_by_user_id' => $enabled ? auth()->id() : null,
        ];

        if ($request->hasFile('royalty_agreement_file')) {
            if ($book->royalty_agreement_file_path) {
                Storage::disk('public')->delete($book->royalty_agreement_file_path);
            }

            $update['royalty_agreement_file_path'] = $request
                ->file('royalty_agreement_file')
                ->store('royalty-documents/' . $book->id, 'public');
        }

        if ($request->hasFile('royalty_contract_file')) {
            if ($book->royalty_contract_file_path) {
                Storage::disk('public')->delete($book->royalty_contract_file_path);
            }

            $update['royalty_contract_file_path'] = $request
                ->file('royalty_contract_file')
                ->store('royalty-documents/' . $book->id, 'public');
        }

        if (!$enabled) {
            if ($book->royalty_agreement_file_path) {
                Storage::disk('public')->delete($book->royalty_agreement_file_path);
            }

            if ($book->royalty_contract_file_path) {
                Storage::disk('public')->delete($book->royalty_contract_file_path);
            }

            $update['royalty_agreement_file_path'] = null;
            $update['royalty_contract_file_path'] = null;
        }

        $book->update($update);

        return back()->with('success', 'Konfigurasi distribusi & royalti buku berhasil diperbarui.');
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
