<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\AuthorInvoice;
use App\Models\AuthorServiceOrder;
use App\Models\Book;
use App\Models\AdditionalService;
use App\Models\PrintPriceRule;
use App\Models\PublishingPackage;
use App\Services\ManuscriptA4PageCounterService;
use App\Services\PublishingOverageService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class AuthorOrderController extends Controller
{
    public function index(RajaOngkirService $rajaOngkir)
    {
        $user = auth()->user();

        $completedBooks = Book::where('author_user_id', $user->id)
            ->where('workflow_status', 'selesai')
            ->with('publishingPackage')
            ->orderBy('judul')
            ->get();

        $packages = PublishingPackage::orderBy('name')->get();
        $printRules = PrintPriceRule::where('is_active', true)->orderBy('name')->get();
        $additionalServices = AdditionalService::where('is_active', true)->orderBy('name')->get();
        $provinceResult = $rajaOngkir->provincesWithMeta();
        $provinces = $provinceResult['data'];

        $orders = AuthorBookOrder::with(['book', 'invoice', 'package', 'printPriceRule'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $accumulatedPayments = AuthorInvoice::forAuthor($user->id)
            ->where('status', 'paid')
            ->sum('amount');

        return view('author.orders.index', compact(
            'completedBooks',
            'packages',
            'printRules',
            'additionalServices',
            'provinces',
            'provinceResult',
            'orders',
            'accumulatedPayments'
        ));
    }

    public function cities(Request $request, RajaOngkirService $rajaOngkir)
    {
        $request->validate([
            'province_id' => ['required', 'string', 'max:16'],
        ]);

        $result = $rajaOngkir->citiesWithMeta($request->province_id);

        return response()->json($result);
    }

    public function buyPackage(
        Request $request,
        ManuscriptA4PageCounterService $pageCounter,
        PublishingOverageService $overageService
    ) {
        $user = auth()->user();

        if (!$user->isAuthorProfileComplete()) {
            return back()->with('warning', 'Lengkapi data identitas penulis sebelum membeli paket baru.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publishing_package_id' => ['required', 'exists:publishing_packages,id'],
            'package_print_price_rule_id' => ['nullable', 'exists:print_price_rules,id'],
            'manuscript_file' => ['required', 'file', 'mimes:docx', 'max:51200'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = Book::create([
            'nomor_naskah' => 'AUTO-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'judul' => $data['title'],
            'penulis_1' => $user->name,
            'author_ktp_number' => $user->ktp_number,
            'jumlah_cetak' => 1,
            'status' => 'draft',
            'workflow_status' => 'draft',
            'author_user_id' => $user->id,
            'publishing_package_id' => $data['publishing_package_id'],
        ]);

        $book->load('publishingPackage');

        $package = $book->publishingPackage;
        if (!$package) {
            return back()->with('danger', 'Paket tidak ditemukan atau tidak aktif.');
        }

        $selectedPrintRule = null;
        $selectedPrintPaper = 'A5';
        if (!empty($data['package_print_price_rule_id'])) {
            $selectedPrintRule = PrintPriceRule::where('is_active', true)->find($data['package_print_price_rule_id']);
            if ($selectedPrintRule && !empty($selectedPrintRule->paper_size)) {
                $selectedPrintPaper = strtoupper((string) $selectedPrintRule->paper_size);
            }
        }

        try {
            $pageSummary = $pageCounter->countFromUploadedFileByPapers(
                $request->file('manuscript_file'),
                $overageService->getTrackedPapers()
            );
        } catch (\Throwable $e) {
            return back()->with('danger', 'Gagal membaca naskah DOCX: ' . $e->getMessage());
        }

        $pageMap = [];
        foreach ($pageSummary as $paper => $count) {
            $pageMap[strtoupper((string) $paper)] = (int) $count;
        }

        $a4Pages = (int) ($pageMap['A4'] ?? 1);
        $a5Pages = (int) ($pageMap['A5'] ?? 1);

        $overage = $overageService->calculateForPackage($package, $pageMap, $selectedPrintPaper);

        $a4Limit = (int) $overage['a4_limit'];
        $overLimitPages = (int) $overage['a4_over_pages'];
        $layoutOverageFee = (float) $overage['layout_fee'];
        $editingOverageFee = (float) $overage['editing_fee'];
        $a5Limit = (int) $overage['print_limit'];
        $printOverLimitPages = (int) $overage['print_over_pages'];
        $printOverageFee = (float) $overage['print_fee'];
        $selectedPrintPages = (int) $overage['selected_print_pages'];
        $selectedPrintPaper = (string) $overage['selected_print_paper'];
        $extraFee = (float) $overage['extra_fee'];

        $book->update([
            'jumlah_halaman' => $a4Pages,
            'manuscript_a4_pages' => $a4Pages,
            'manuscript_a5_pages' => $a5Pages,
            'manuscript_overage_pages' => $overLimitPages,
            'manuscript_print_overage_pages' => $printOverLimitPages,
            'manuscript_layout_overage_fee' => $layoutOverageFee,
            'manuscript_editing_overage_fee' => $editingOverageFee,
            'manuscript_print_overage_fee' => $printOverageFee,
            'package_extra_fee' => $extraFee,
        ]);

        $book->files()
            ->where('type', 'naskah_final')
            ->update([
                'is_active' => false,
            ]);

        $manuscriptFile = $request->file('manuscript_file');
        $filePath = $manuscriptFile->store('books/' . $book->nomor_naskah . '/author-order-manuscript', 'public');

        $latestVersion = (int) ($book->files()
            ->where('type', 'naskah_final')
            ->max('version') ?? 0);

        $book->files()->create([
            'type' => 'naskah_final',
            'original_name' => $manuscriptFile->getClientOriginalName(),
            'note' => 'Upload awal naskah dari Order Penulis (auto-hitungan A4/A5 margin 2 cm).',
            'sender_role' => 'author',
            'file_path' => $filePath,
            'mime_type' => $manuscriptFile->getMimeType(),
            'file_size' => $manuscriptFile->getSize(),
            'is_active' => true,
            'version' => $latestVersion + 1,
        ]);

        $invoice = AuthorInvoice::createPackageInvoice($book);

        $packagePrice = (float) ($package->price ?? 0);
        $total = $packagePrice + $extraFee;

        $notes = trim((string) ($data['notes'] ?? ''));
        if ($overLimitPages > 0) {
            $breakdown = 'A4: ' . $a4Pages
                . ' halaman (limit ' . $a4Limit
                . '). Kelebihan: ' . $overLimitPages
                . ' halaman. Biaya lebih layout Rp ' . number_format($layoutOverageFee, 0, ',', '.')
                . ($package->includes_editing
                    ? ', editing Rp ' . number_format($editingOverageFee, 0, ',', '.')
                    : ', editing tidak termasuk paket')
                . '.';

            $notes = $notes !== '' ? $breakdown . PHP_EOL . $notes : $breakdown;
        }

        if ($printOverLimitPages > 0) {
            $printBreakdown = $selectedPrintPaper . ': ' . $selectedPrintPages
                . ' halaman (limit ' . $a5Limit
                . ', paket cetak aktif). Kelebihan: ' . $printOverLimitPages
                . ' halaman. Biaya lebih cetak Rp ' . number_format($printOverageFee, 0, ',', '.') . '.';

            $notes = $notes !== '' ? $printBreakdown . PHP_EOL . $notes : $printBreakdown;
        }

        AuthorBookOrder::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'publishing_package_id' => $book->publishing_package_id,
            'print_price_rule_id' => $selectedPrintRule?->id,
            'author_invoice_id' => $invoice?->id,
            'order_type' => 'new_package',
            'title' => $book->judul,
            'pages' => $a4Pages,
            'manuscript_a4_pages' => $a4Pages,
            'manuscript_a5_pages' => $selectedPrintPages,
            'a4_page_limit' => $a4Limit,
            'a5_page_limit' => $a5Limit,
            'over_limit_pages' => $overLimitPages,
            'print_over_limit_pages' => $printOverLimitPages,
            'layout_over_limit_fee' => $layoutOverageFee,
            'editing_over_limit_fee' => $editingOverageFee,
            'print_over_limit_fee' => $printOverageFee,
            'quantity' => 1,
            'unit_price' => $packagePrice,
            'subtotal' => $total,
            'shipping_cost' => 0,
            'total_amount' => $total,
            'status' => $invoice ? 'invoiced' : 'pending',
            'notes' => $notes !== '' ? $notes : null,
        ]);

        $message = ($overLimitPages > 0 || $printOverLimitPages > 0)
            ? 'Order paket berhasil dibuat. Halaman A4/A5 terhitung otomatis dan biaya lebih (jika ada) sudah ditambahkan. Invoice DP 50% sudah diterbitkan.'
            : 'Pembelian paket baru berhasil dibuat. Invoice DP 50% sudah diterbitkan.';

        return back()->with('success', $message);
    }

    public function previewPackageCharge(
        Request $request,
        ManuscriptA4PageCounterService $pageCounter,
        PublishingOverageService $overageService
    ) {
        $data = $request->validate([
            'publishing_package_id' => ['required', 'exists:publishing_packages,id'],
            'package_print_price_rule_id' => ['nullable', 'exists:print_price_rules,id'],
            'manuscript_file' => ['required', 'file', 'mimes:docx', 'max:51200'],
        ]);

        $package = PublishingPackage::findOrFail((int) $data['publishing_package_id']);

        $selectedPrintRule = null;
        $selectedPrintPaper = 'A5';
        if (!empty($data['package_print_price_rule_id'])) {
            $selectedPrintRule = PrintPriceRule::where('is_active', true)->find($data['package_print_price_rule_id']);
            if ($selectedPrintRule && !empty($selectedPrintRule->paper_size)) {
                $selectedPrintPaper = strtoupper((string) $selectedPrintRule->paper_size);
            }
        }

        try {
            $summary = $pageCounter->countFromUploadedFileByPapers(
                $request->file('manuscript_file'),
                $overageService->getTrackedPapers()
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal membaca naskah DOCX: ' . $e->getMessage(),
            ], 422);
        }

        $pageMap = [];
        foreach ($summary as $paper => $count) {
            $pageMap[strtoupper((string) $paper)] = (int) $count;
        }

        $a4Pages = (int) ($pageMap['A4'] ?? 1);
        $a5Pages = (int) ($pageMap['A5'] ?? 1);
        $overage = $overageService->calculateForPackage($package, $pageMap, $selectedPrintPaper);

        $packagePrice = (float) ($package->price ?? 0);

        return response()->json([
            'ok' => true,
            'data' => [
                'a4_pages' => $a4Pages,
                'a5_pages' => $a5Pages,
                'a4_limit' => (int) $overage['a4_limit'],
                'a4_over_pages' => (int) $overage['a4_over_pages'],
                'selected_print_paper' => (string) $overage['selected_print_paper'],
                'selected_print_pages' => (int) $overage['selected_print_pages'],
                'print_limit' => (int) $overage['print_limit'],
                'print_over_pages' => (int) $overage['print_over_pages'],
                'print_overage_rate' => (float) $overage['print_overage_rate'],
                'layout_fee' => (float) $overage['layout_fee'],
                'editing_fee' => (float) $overage['editing_fee'],
                'print_fee' => (float) $overage['print_fee'],
                'extra_fee' => (float) $overage['extra_fee'],
                'package_price' => $packagePrice,
                'total' => $packagePrice + (float) $overage['extra_fee'],
                'includes_editing' => (bool) $package->includes_editing,
                'supports_print' => (bool) $package->supports_print,
                'selected_print_rule_name' => $selectedPrintRule?->name,
            ],
        ]);
    }

    public function reorderPrint(Request $request, RajaOngkirService $rajaOngkir)
    {
        $user = auth()->user();

        if (!$user->isAuthorProfileComplete()) {
            return back()->with('warning', 'Lengkapi data identitas penulis sebelum memesan cetak ulang.');
        }

        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'print_price_rule_id' => ['required', 'exists:print_price_rules,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'destination_city_id' => ['required', 'string', 'max:16'],
            'destination_city' => ['required', 'string', 'max:100'],
            'destination_province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'shipping_address' => ['required', 'string'],
            'courier' => ['required', 'in:jne,tiki,pos'],
            'notes' => ['nullable', 'string'],
        ]);

        $book = Book::where('author_user_id', $user->id)
            ->where('workflow_status', 'selesai')
            ->with('publishingPackage')
            ->findOrFail($data['book_id']);

        $requiresPrintAdaptation = (bool) ($book->publishingPackage && !$book->publishingPackage->supports_print);

        $rule = PrintPriceRule::where('is_active', true)->findOrFail($data['print_price_rule_id']);

        $pages = (int) ($book->jumlah_halaman ?: 100);
        $quantity = (int) $data['quantity'];
        $unitPrice = $rule->calculateUnitPrice($pages);
        $subtotal = $unitPrice * $quantity;

        $shipping = $rajaOngkir->estimateCost(
            $data['destination_city_id'],
            $rule->calculateWeight($quantity),
            $data['courier']
        );

        $shippingCost = (float) ($shipping['cost'] ?? 0);
        $total = $subtotal + $shippingCost;

        $invoice = AuthorInvoice::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'type' => 'additional',
            'description' => 'Pesanan cetak ulang: ' . $book->judul . ' (' . $quantity . ' eks)',
            'amount' => $total,
            'status' => 'pending',
            'notes' => 'Biaya cetak ulang + ongkir (' . strtoupper($data['courier']) . ' ' . ($shipping['service'] ?? '-') . ').',
        ]);

        $notes = trim((string) ($data['notes'] ?? ''));
        if ($requiresPrintAdaptation) {
            $adaptationNote = 'AUTO_PRINT_ADAPTATION_REQUIRED: Buku berasal dari paket ebook-only, perlu penyesuaian naskah ke format cetak sebelum produksi.';
            $notes = $notes !== '' ? $adaptationNote . PHP_EOL . $notes : $adaptationNote;
        }

        AuthorBookOrder::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'print_price_rule_id' => $rule->id,
            'author_invoice_id' => $invoice->id,
            'order_type' => 'reprint',
            'title' => $book->judul,
            'pages' => $pages,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total_amount' => $total,
            'destination_province' => $data['destination_province'],
            'destination_city' => $data['destination_city'],
            'destination_city_id' => $data['destination_city_id'],
            'postal_code' => $data['postal_code'] ?? null,
            'shipping_address' => $data['shipping_address'],
            'courier' => strtoupper($data['courier']),
            'courier_service' => $shipping['service'] ?? null,
            'etd' => $shipping['etd'] ?? null,
            'shipping_payload' => $shipping['raw'] ?? null,
            'status' => 'invoiced',
            'notes' => $notes !== '' ? $notes : null,
        ]);

        $message = $requiresPrintAdaptation
            ? 'Pesanan cetak berhasil dibuat untuk buku paket ebook-only. Setelah DP lunas, order masuk workspace percetakan dengan penanda perlu penyesuaian naskah cetak.'
            : 'Pesanan cetak ulang berhasil dibuat. Invoice telah diterbitkan.';

        return back()->with('success', $message);
    }

    public function orderService(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'book_id' => ['nullable', 'exists:books,id'],
            'additional_service_id' => ['required', 'exists:additional_services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $service = AdditionalService::where('is_active', true)
            ->findOrFail($data['additional_service_id']);

        $qty = (int) $data['quantity'];
        $total = (float) $service->price * $qty;

        $bookId = null;
        if (!empty($data['book_id'])) {
            $bookId = Book::where('author_user_id', $user->id)->findOrFail($data['book_id'])->id;
        }

        $invoice = AuthorInvoice::create([
            'book_id' => $bookId,
            'user_id' => $user->id,
            'type' => 'additional',
            'description' => 'Layanan tambahan: ' . $service->name,
            'amount' => $total,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        AuthorServiceOrder::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'additional_service_id' => $service->id,
            'author_invoice_id' => $invoice->id,
            'quantity' => $qty,
            'unit_price' => $service->price,
            'total_amount' => $total,
            'status' => 'invoiced',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Pesanan layanan tambahan berhasil dibuat. Invoice diterbitkan.');
    }

}
