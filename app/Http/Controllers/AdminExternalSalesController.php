<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ExternalSalesRecord;
use App\Models\LegacyBook;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminExternalSalesController extends Controller
{
    public function index()
    {
        $selectedBookId = (int) request('book_id');
        $selectedLegacyBookId = (int) request('legacy_book_id');

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

        $legacyBooks = LegacyBook::orderBy('title')->get();
        $eligibleLegacyBooks = $legacyBooks->where('royalty_enabled', true)->values();
        $selectedLegacyBook = $selectedLegacyBookId ? $legacyBooks->firstWhere('id', $selectedLegacyBookId) : null;

        $records = ExternalSalesRecord::with(['book', 'legacyBook', 'inputBy'])
            ->latest('sold_at')
            ->latest('id')
            ->paginate(30);

        $allSales = ExternalSalesRecord::with([
            'book:id,selling_price,royalty_rate,royalty_enabled',
            'legacyBook:id,list_price,royalty_rate,royalty_enabled',
        ])->get(['book_id', 'legacy_book_id', 'channel', 'quantity', 'unit_price', 'gross_amount']);

        $totalRoyalty = $allSales->sum(function (ExternalSalesRecord $row) {
            $bookPrice = (float) (optional($row->book)->selling_price ?: 0);
            $legacyPrice = (float) (optional($row->legacyBook)->list_price ?: 0);
            $unitPrice = $bookPrice > 0 ? $bookPrice : ($legacyPrice > 0 ? $legacyPrice : (float) $row->unit_price);

            $rate = $row->book
                ? $row->book->royaltyRate()
                : ($row->legacyBook ? $row->legacyBook->royaltyRate() : 0.20);

            return ((int) $row->quantity) * $unitPrice * $rate;
        });

        $channelSummary = $allSales
            ->groupBy('channel')
            ->map(fn($rows) => [
                'gross' => (float) $rows->sum('gross_amount'),
                'count' => (int) $rows->count(),
            ])
            ->sortByDesc('gross');

        $totals = [
            'gross' => (float) $allSales->sum('gross_amount'),
            'royalty' => (float) $totalRoyalty,
            'books_active_royalty' => (int) $eligibleBooks->count() + (int) $eligibleLegacyBooks->count(),
        ];

        return view('admin.external-sales.index', compact('books', 'eligibleBooks', 'records', 'totals', 'selectedBook', 'legacyBooks', 'eligibleLegacyBooks', 'selectedLegacyBook', 'channelSummary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['nullable', 'required_without:legacy_book_id', 'exists:books,id'],
            'legacy_book_id' => ['nullable', 'required_without:book_id', 'exists:legacy_books,id'],
            'channel' => ['required', 'in:amazon,google_play_books,website,marketplace,other'],
            'format' => ['required', 'in:ebook,print'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'gross_amount' => ['nullable', 'numeric', 'min:0'],
            'sold_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = !empty($data['book_id']) ? Book::findOrFail($data['book_id']) : null;
        $legacyBook = !empty($data['legacy_book_id']) ? LegacyBook::findOrFail($data['legacy_book_id']) : null;

        if ($book && !$book->isRoyaltyEligible()) {
            return back()->with('warning', 'Buku ini belum diaktifkan untuk program distribusi royalti.');
        }

        if ($legacyBook && !$legacyBook->royalty_enabled) {
            return back()->with('warning', 'Buku katalog legacy ini belum diaktifkan untuk program distribusi royalti.');
        }

        if ($book && !$book->canDistributeByChannel((string) $data['channel'], (string) $data['format'])) {
            return back()->with('warning', 'Channel/format ini belum diizinkan pada konfigurasi distribusi buku tersebut.');
        }

        if ($legacyBook) {
            if ($data['format'] === 'ebook' && !$legacyBook->distribution_ebook) {
                return back()->with('warning', 'Distribusi ebook belum diizinkan untuk buku katalog ini.');
            }

            if ($data['channel'] === 'marketplace' && !$legacyBook->distribution_marketplace) {
                return back()->with('warning', 'Distribusi marketplace belum diizinkan untuk buku katalog ini.');
            }

            if ($data['channel'] !== 'marketplace' && !$legacyBook->distribution_online) {
                return back()->with('warning', 'Distribusi online belum diizinkan untuk buku katalog ini.');
            }
        }

        $gross = isset($data['gross_amount']) && $data['gross_amount'] !== null
            ? (float) $data['gross_amount']
            : ((int) $data['quantity'] * (float) $data['unit_price']);

        ExternalSalesRecord::create([
            'book_id' => $data['book_id'] ?? null,
            'legacy_book_id' => $data['legacy_book_id'] ?? null,
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

    public function updateRoyaltyProgram(Request $request, NotificationService $notifications)
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

            $update['royalty_contract_version'] = ((int) ($book->royalty_contract_version ?? 0)) + 1;
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

        $agreementPath = $update['royalty_agreement_file_path'] ?? $book->royalty_agreement_file_path;
        $contractPath = $update['royalty_contract_file_path'] ?? $book->royalty_contract_file_path;

        if ($enabled && $agreementPath && $contractPath) {
            $nextVersion = (int) ($book->royalty_contract_version ?? 1);

            $book->update([
                'royalty_contract_status' => 'pending_author_accept',
                'royalty_contract_version' => $nextVersion,
                'royalty_contract_sent_at' => now(),
            ]);

            if ($book->author_user_id) {
                $notifications->send(
                    $book->author_user_id,
                    'Kontrak Royalti Siap Ditinjau',
                    'Kontrak royalti untuk buku "' . $book->judul . '" telah diterbitkan. Silakan tinjau dan konfirmasi di dashboard royalti Anda.',
                    $book->id
                );
            }
        }

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
