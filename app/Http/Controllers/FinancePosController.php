<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\LegacyBook;
use App\Models\PosInvoice;
use App\Models\PosOrder;
use App\Models\PrintPriceRule;
use App\Models\PublishingPackage;
use App\Models\StoreCatalogItem;
use App\Models\User;
use App\Services\ManuscriptA4PageCounterService;
use App\Services\PublishingOverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancePosController extends Controller
{
    public function index(Request $request)
    {
        $query = PosOrder::with([
            'items.publishingPackage',
            'invoices',
            'linkedUser',
            'linkedBook',
        ])->latest();

        if ($request->filled('q')) {
            $keyword = trim((string) $request->q);
            $query->where(function ($inner) use ($keyword) {
                $inner->where('order_number', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhere('manuscript_title', 'like', "%{$keyword}%")
                    ->orWhere('service_order_ref', 'like', "%{$keyword}%")
                    ->orWhere('marketing_ref', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source_channel')) {
            $query->where('source_channel', $request->source_channel);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'orders_count' => PosOrder::count(),
            'revenue_total' => (float) PosOrder::sum('total_amount'),
            'invoice_pending' => (float) PosInvoice::where('status', 'pending')->sum('amount'),
            'invoice_paid' => (float) PosInvoice::where('status', 'paid')->sum('amount'),
            'overdue_count' => PosInvoice::where('status', 'pending')->whereDate('due_date', '<', now()->toDateString())->count(),
            'pending_term_1' => (float) PosInvoice::where('status', 'pending')->where('installment_number', 1)->sum('amount'),
            'pending_term_2' => (float) PosInvoice::where('status', 'pending')->where('installment_number', 2)->sum('amount'),
        ];

        $totalInvoices = PosInvoice::count();
        $paidInvoices = PosInvoice::where('status', 'paid')->count();
        $stats['invoice_paid_ratio'] = $totalInvoices > 0
            ? round(($paidInvoices / $totalInvoices) * 100, 1)
            : 0;

        $packages = PublishingPackage::orderBy('name')->get(['id', 'name', 'price']);
        $productOptions = $this->buildProductOptions();
        $printRules = PrintPriceRule::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'paper_size']);

        return view('finance.pos.index', compact('orders', 'stats', 'packages', 'productOptions', 'printRules'));
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'manuscript_title' => ['nullable', 'string', 'max:255'],
            'author_ktp_number' => ['nullable', 'string', 'max:32'],
            'service_order_ref' => ['nullable', 'string', 'max:64'],
            'marketing_ref' => ['nullable', 'string', 'max:255'],
            'source_channel' => ['required', 'in:offline,whatsapp,instagram,marketplace,website,other'],
            'discount_scope' => ['nullable', 'in:global,unit,item'],
            'discount_type' => ['nullable', 'in:nominal,percent'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'publishing_detail_print_price_rule_id' => ['nullable', 'integer', 'exists:print_price_rules,id'],
            'publishing_detail_manual_a4_pages' => ['nullable', 'integer', 'min:1'],
            'publishing_detail_manual_a5_pages' => ['nullable', 'integer', 'min:1'],
            'publishing_detail_manuscript_file' => ['nullable', 'file', 'mimes:docx', 'max:51200'],
            'status' => ['nullable', 'in:draft,confirmed,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:publishing_service,book_print,ebook,extra_service'],
            'items.*.publishing_package_id' => ['nullable', 'integer', 'exists:publishing_packages,id'],
            'items.*.product_source_type' => ['nullable', 'in:store_catalog,legacy_book,book'],
            'items.*.product_source_id' => ['nullable', 'integer', 'min:1'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.item_description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:nominal,percent'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $discountScope = (string) ($data['discount_scope'] ?? 'global');
        $normalizedItems = $this->normalizeItemsFromRequest($data['items']);

        if (empty($normalizedItems)) {
            return back()->withInput()->with('warning', 'Minimal satu item valid dibutuhkan untuk order POS.');
        }

        $hasPublishingService = collect($normalizedItems)->contains(fn($x) => $x['item_type'] === 'publishing_service');
        if ($hasPublishingService && empty($data['manuscript_title'])) {
            return back()->withInput()->with('warning', 'Untuk Jasa Penerbitan, judul naskah wajib diisi.');
        }

        $publishingMetadata = null;
        if ($hasPublishingService) {
            $publishingMetadata = $this->buildPublishingMetadataAndOverageItem($request, $data, $normalizedItems);
            if ($publishingMetadata !== null && (float) ($publishingMetadata['extra_fee'] ?? 0) > 0) {
                $extraFee = (float) $publishingMetadata['extra_fee'];
                $normalizedItems[] = [
                    'item_type' => 'extra_service',
                    'publishing_package_id' => null,
                    'product_source_type' => null,
                    'product_source_id' => null,
                    'item_name' => 'Biaya Lebih Halaman Otomatis (' . ($publishingMetadata['selected_print_paper'] ?? 'A5') . ')',
                    'item_description' => 'Auto dari detail jasa penerbitan: layout/editing/cetak.',
                    'quantity' => 1,
                    'unit_price' => $extraFee,
                    'discount_type' => 'nominal',
                    'discount_input' => 0,
                    'discount_amount' => 0,
                    'line_total_before_discount' => $extraFee,
                    'line_total' => $extraFee,
                    'extra_service_amount' => $extraFee,
                ];
            }
        }

        DB::transaction(function () use ($data, $normalizedItems, $discountScope, $publishingMetadata): void {
            $linkedUserId = $this->resolveLinkedUserId(
                $data['customer_email'] ?? null,
                $data['customer_phone'] ?? null
            );

            $discountType = (string) ($data['discount_type'] ?? 'nominal');
            $discountInput = (float) ($data['discount_amount'] ?? 0);

            $order = PosOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'manuscript_title' => $data['manuscript_title'] ?? null,
                'author_ktp_number' => $data['author_ktp_number'] ?? null,
                'service_order_ref' => $data['service_order_ref'] ?? null,
                'marketing_ref' => $data['marketing_ref'] ?? null,
                'source_channel' => $data['source_channel'],
                'status' => $data['status'] ?? 'confirmed',
                'discount_scope' => $discountScope,
                'discount_type' => $discountType,
                'discount_input' => $discountInput,
                'discount_amount' => 0,
                'publishing_metadata' => $publishingMetadata,
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => auth()->id(),
                'linked_user_id' => $linkedUserId,
                'subtotal' => 0,
                'total_amount' => 0,
            ]);

            foreach ($normalizedItems as $item) {
                $order->items()->create($item);
            }

            $this->recalculateOrderTotals($order, $discountScope, $discountType, $discountInput);
        });

        return back()->with('success', 'Order POS berhasil dibuat. Produk buku/ebook dapat otomatis terdeteksi dari katalog yang sudah ada.');
    }

    public function previewPublishingOverage(Request $request)
    {
        $data = $request->validate([
            'publishing_package_id' => ['required', 'integer', 'exists:publishing_packages,id'],
            'publishing_detail_print_price_rule_id' => ['nullable', 'integer', 'exists:print_price_rules,id'],
            'publishing_detail_manual_a4_pages' => ['nullable', 'integer', 'min:1'],
            'publishing_detail_manual_a5_pages' => ['nullable', 'integer', 'min:1'],
            'publishing_detail_manuscript_file' => ['nullable', 'file', 'mimes:docx', 'max:51200'],
        ]);

        $metadata = $this->calculatePublishingMetadataFromInputs(
            $request,
            (int) $data['publishing_package_id'],
            $data
        );

        if ($metadata === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Paket jasa penerbitan tidak valid.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'data' => $metadata,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, array<string, mixed>> $normalizedItems
     * @return array<string, mixed>|null
     */
    private function buildPublishingMetadataAndOverageItem(Request $request, array $data, array $normalizedItems): ?array
    {
        $publishingItem = collect($normalizedItems)->firstWhere('item_type', 'publishing_service');
        if (!is_array($publishingItem)) {
            return null;
        }

        $packageId = (int) ($publishingItem['publishing_package_id'] ?? 0);
        return $this->calculatePublishingMetadataFromInputs($request, $packageId, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    private function calculatePublishingMetadataFromInputs(Request $request, int $packageId, array $data): ?array
    {
        $package = PublishingPackage::query()->find($packageId);
        if (!$package) {
            return null;
        }

        $selectedPaper = 'A5';
        $selectedRule = null;
        if (!empty($data['publishing_detail_print_price_rule_id'])) {
            $selectedRule = PrintPriceRule::where('is_active', true)
                ->find((int) $data['publishing_detail_print_price_rule_id']);
            if ($selectedRule && !empty($selectedRule->paper_size)) {
                $selectedPaper = strtoupper((string) $selectedRule->paper_size);
            }
        }

        $pageCounts = [
            'A4' => max(1, (int) ($data['publishing_detail_manual_a4_pages'] ?? 1)),
            'A5' => max(1, (int) ($data['publishing_detail_manual_a5_pages'] ?? 1)),
        ];

        /** @var PublishingOverageService $overageService */
        $overageService = app(PublishingOverageService::class);

        if ($request->hasFile('publishing_detail_manuscript_file')) {
            /** @var ManuscriptA4PageCounterService $counter */
            $counter = app(ManuscriptA4PageCounterService::class);

            try {
                $countedPages = $counter->countFromUploadedFileByPapers(
                    $request->file('publishing_detail_manuscript_file'),
                    $overageService->getTrackedPapers()
                );

                foreach ($countedPages as $paper => $count) {
                    $pageCounts[strtoupper((string) $paper)] = max(1, (int) $count);
                }
            } catch (\Throwable $e) {
                // Keep manual page counts when file parsing fails.
            }
        }

        $overage = $overageService->calculateForPackage($package, $pageCounts, $selectedPaper);

        return [
            'print_price_rule_id' => $selectedRule?->id,
            'print_price_rule_name' => $selectedRule?->name,
            'selected_print_paper' => (string) ($overage['selected_print_paper'] ?? 'A5'),
            'selected_print_pages' => (int) ($overage['selected_print_pages'] ?? 1),
            'a4_pages' => (int) ($overage['a4_pages'] ?? 1),
            'a4_limit' => (int) ($overage['a4_limit'] ?? 125),
            'a4_over_pages' => (int) ($overage['a4_over_pages'] ?? 0),
            'print_limit' => (int) ($overage['print_limit'] ?? 100),
            'print_over_pages' => (int) ($overage['print_over_pages'] ?? 0),
            'print_overage_rate' => (float) ($overage['print_overage_rate'] ?? 500),
            'layout_fee' => (float) ($overage['layout_fee'] ?? 0),
            'editing_fee' => (float) ($overage['editing_fee'] ?? 0),
            'print_fee' => (float) ($overage['print_fee'] ?? 0),
            'extra_fee' => (float) ($overage['extra_fee'] ?? 0),
        ];
    }

    public function addExtraService(Request $request, PosOrder $order)
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'item_description' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $lineTotal = ((int) $data['quantity']) * (float) $data['unit_price'];

        $order->items()->create([
            'item_type' => 'extra_service',
            'publishing_package_id' => null,
            'product_source_type' => null,
            'product_source_id' => null,
            'item_name' => $data['item_name'],
            'item_description' => $data['item_description'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount_type' => 'nominal',
            'discount_input' => 0,
            'discount_amount' => 0,
            'line_total_before_discount' => $lineTotal,
            'line_total' => $lineTotal,
            'extra_service_amount' => $lineTotal,
        ]);

        $this->recalculateOrderTotals(
            $order,
            (string) ($order->discount_scope ?? 'global'),
            (string) ($order->discount_type ?? 'nominal'),
            (float) ($order->discount_input ?? $order->discount_amount)
        );

        return back()->with('success', 'Layanan extra berhasil ditambahkan ke order POS.');
    }

    public function createInvoice(Request $request, PosOrder $order)
    {
        $data = $request->validate([
            'installment_number' => ['required', 'integer', 'in:1,2'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $exists = $order->invoices()
            ->where('installment_number', (int) $data['installment_number'])
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Invoice termin ' . $data['installment_number'] . ' untuk order ini sudah ada.');
        }

        $description = $data['description']
            ?? ('Invoice POS Termin ' . $data['installment_number'] . ' - ' . $order->order_number);

        $order->invoices()->create([
            'installment_number' => $data['installment_number'],
            'description' => $description,
            'amount' => $data['amount'],
            'status' => 'pending',
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Invoice POS termin ' . $data['installment_number'] . ' berhasil dibuat.');
    }

    public function updateInvoice(Request $request, PosInvoice $invoice)
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->update([
            'description' => $data['description'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Invoice POS berhasil diperbarui.');
    }

    public function markInvoicePaid(Request $request, PosInvoice $invoice)
    {
        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:32'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice POS ini sudah lunas.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $data['payment_method'] ?? $invoice->payment_method,
            'payment_reference' => $data['payment_reference'] ?? $invoice->payment_reference,
            'verified_by_user_id' => auth()->id(),
            'notes' => $data['notes'] ?? $invoice->notes,
        ]);

        $invoice->load(['order.items']);
        $this->syncOrderToProductionIfPublishingService($invoice->order, (int) $invoice->installment_number);

        return back()->with('success', 'Invoice POS #' . $invoice->invoice_number . ' ditandai lunas.');
    }

    public function markInvoicePending(PosInvoice $invoice)
    {
        $invoice->update([
            'status' => 'pending',
            'paid_at' => null,
            'verified_by_user_id' => null,
        ]);

        return back()->with('success', 'Invoice POS #' . $invoice->invoice_number . ' dikembalikan ke pending.');
    }

    private function buildProductOptions(): array
    {
        $options = [];

        $catalogItems = StoreCatalogItem::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();

        foreach ($catalogItems as $item) {
            if ($item->isPrint()) {
                $options[] = [
                    'source_type' => 'store_catalog',
                    'source_id' => $item->id,
                    'item_type' => 'book_print',
                    'name' => '[Katalog] ' . $item->title . ' (Print)',
                    'price' => (float) $item->finalPriceForFormat('print'),
                ];
            }

            if ($item->isEbook()) {
                $options[] = [
                    'source_type' => 'store_catalog',
                    'source_id' => $item->id,
                    'item_type' => 'ebook',
                    'name' => '[Katalog] ' . $item->title . ' (Ebook)',
                    'price' => (float) $item->finalPriceForFormat('ebook'),
                ];
            }
        }

        $legacyBooks = LegacyBook::query()
            ->orderBy('title')
            ->get(['id', 'title', 'list_price', 'distribution_online', 'distribution_ebook']);

        foreach ($legacyBooks as $legacy) {
            if ($legacy->distribution_online) {
                $options[] = [
                    'source_type' => 'legacy_book',
                    'source_id' => $legacy->id,
                    'item_type' => 'book_print',
                    'name' => '[Legacy] ' . $legacy->title . ' (Print)',
                    'price' => (float) ($legacy->list_price ?? 0),
                ];
            }

            if ($legacy->distribution_ebook) {
                $options[] = [
                    'source_type' => 'legacy_book',
                    'source_id' => $legacy->id,
                    'item_type' => 'ebook',
                    'name' => '[Legacy] ' . $legacy->title . ' (Ebook)',
                    'price' => (float) ($legacy->list_price ?? 0),
                ];
            }
        }

        $finishedBooks = Book::query()
            ->whereIn('workflow_status', ['isbn_approved', 'selesai'])
            ->orderBy('judul')
            ->get(['id', 'judul', 'selling_price', 'final_ebook_link']);

        foreach ($finishedBooks as $book) {
            $printPrice = (float) ($book->selling_price ?? 0);

            if ($printPrice > 0) {
                $options[] = [
                    'source_type' => 'book',
                    'source_id' => $book->id,
                    'item_type' => 'book_print',
                    'name' => '[Naskah Selesai] ' . $book->judul . ' (Print)',
                    'price' => $printPrice,
                ];

                if (!empty($book->final_ebook_link)) {
                    $options[] = [
                        'source_type' => 'book',
                        'source_id' => $book->id,
                        'item_type' => 'ebook',
                        'name' => '[Naskah Selesai] ' . $book->judul . ' (Ebook)',
                        'price' => $printPrice,
                    ];
                }
            }
        }

        return $options;
    }

    private function syncOrderToProductionIfPublishingService(PosOrder $order, int $installmentNumber): void
    {
        if ($order->linked_book_id) {
            return;
        }

        if ($installmentNumber !== 1) {
            return;
        }

        $publishingItem = $order->items->firstWhere('item_type', 'publishing_service');
        if (!$publishingItem) {
            return;
        }

        $book = Book::create([
            'nomor_naskah' => $this->generateManuscriptNumber(),
            'judul' => $order->manuscript_title ?: ('Naskah POS ' . $order->order_number),
            'penulis_1' => $order->customer_name,
            'author_ktp_number' => $order->author_ktp_number ?: '-',
            'jumlah_halaman' => 1,
            'manuscript_a4_pages' => 1,
            'jumlah_cetak' => 1,
            'status' => 'draft',
            'workflow_status' => 'draft',
            'author_user_id' => $order->linked_user_id,
            'publishing_package_id' => $publishingItem->publishing_package_id,
            'package_extra_fee' => $this->calculateExtraServiceFee($order),
        ]);

        $order->update([
            'linked_book_id' => $book->id,
            'production_synced_at' => now(),
        ]);
    }

    private function calculateExtraServiceFee(PosOrder $order): float
    {
        return (float) $order->items
            ->where('item_type', 'extra_service')
            ->sum('line_total');
    }

    private function recalculateOrderTotals(
        PosOrder $order,
        string $discountScope = 'global',
        string $discountType = 'nominal',
        ?float $discountInput = null
    ): void {
        $items = $order->items()->get();
        $subtotal = (float) $items->sum('line_total');
        $rawDiscount = max(0, (float) ($discountInput ?? $order->discount_amount));
        $scope = in_array($discountScope, ['global', 'unit', 'item'], true) ? $discountScope : 'global';

        $discount = 0;

        if ($scope === 'item') {
            foreach ($items as $item) {
                $lineTotal = (float) $item->line_total;
                $itemType = in_array((string) $item->discount_type, ['nominal', 'percent'], true)
                    ? (string) $item->discount_type
                    : 'nominal';
                $itemInput = max(0, (float) ($item->discount_input ?? 0));

                if ($itemType === 'percent') {
                    $itemDiscount = round(min(100, $itemInput) * $lineTotal / 100, 2);
                } else {
                    $itemDiscount = min($lineTotal, $itemInput);
                }

                $item->update([
                    'discount_type' => $itemType,
                    'discount_input' => $itemInput,
                    'discount_amount' => $itemDiscount,
                    'line_total_before_discount' => $lineTotal,
                ]);

                $discount += $itemDiscount;
            }
        } elseif ($scope === 'unit') {
            $items->each(function ($item): void {
                $lineTotal = (float) $item->line_total;
                $item->update([
                    'discount_type' => 'nominal',
                    'discount_input' => 0,
                    'discount_amount' => 0,
                    'line_total_before_discount' => $lineTotal,
                ]);
            });

            foreach ($items as $item) {
                $qty = max(1, (int) $item->quantity);
                $unitPrice = max(0, (float) $item->unit_price);
                $lineTotal = max(0, (float) $item->line_total);

                if ($discountType === 'percent') {
                    $unitDiscount = min(100, $rawDiscount) * $unitPrice / 100;
                    $lineDiscount = $unitDiscount * $qty;
                } else {
                    $lineDiscount = min($unitPrice, $rawDiscount) * $qty;
                }

                $discount += min($lineTotal, $lineDiscount);
            }
        } else {
            $items->each(function ($item): void {
                $lineTotal = (float) $item->line_total;
                $item->update([
                    'discount_type' => 'nominal',
                    'discount_input' => 0,
                    'discount_amount' => 0,
                    'line_total_before_discount' => $lineTotal,
                ]);
            });

            if ($discountType === 'percent') {
                $percent = min(100, $rawDiscount);
                $discount = round($subtotal * ($percent / 100), 2);
            } else {
                $discount = min($subtotal, $rawDiscount);
            }
        }

        $discount = min($subtotal, max(0, $discount));

        $order->update([
            'subtotal' => $subtotal,
            'discount_scope' => $scope,
            'discount_type' => $discountType,
            'discount_input' => $rawDiscount,
            'discount_amount' => $discount,
            'total_amount' => max(0, $subtotal - $discount),
        ]);
    }

    private function normalizeItemsFromRequest(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $type = (string) ($item['item_type'] ?? '');
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $description = $item['item_description'] ?? null;

            if ($type === 'publishing_service') {
                $packageId = (int) ($item['publishing_package_id'] ?? 0);
                $package = PublishingPackage::query()->find($packageId);

                if (!$package) {
                    continue;
                }

                $unitPrice = (float) ($package->price ?? 0);
                $lineTotal = $quantity * $unitPrice;

                $normalized[] = [
                    'item_type' => 'publishing_service',
                    'publishing_package_id' => $package->id,
                    'product_source_type' => null,
                    'product_source_id' => null,
                    'item_name' => 'Jasa Penerbitan - ' . $package->name,
                    'item_description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_type' => in_array((string) ($item['discount_type'] ?? ''), ['nominal', 'percent'], true)
                        ? (string) $item['discount_type']
                        : 'nominal',
                    'discount_input' => max(0, (float) ($item['discount_amount'] ?? 0)),
                    'discount_amount' => 0,
                    'line_total_before_discount' => $lineTotal,
                    'line_total' => $lineTotal,
                    'extra_service_amount' => 0,
                ];
                continue;
            }

            $sourceType = $item['product_source_type'] ?? null;
            $sourceId = !empty($item['product_source_id']) ? (int) $item['product_source_id'] : null;
            $sourceResolved = $this->resolveProductSource($type, $sourceType, $sourceId);

            $name = trim((string) ($item['item_name'] ?? ''));
            $unitPriceInput = isset($item['unit_price']) ? (float) $item['unit_price'] : null;

            if ($sourceResolved) {
                if ($name === '') {
                    $name = $sourceResolved['name'];
                }

                if ($unitPriceInput === null || $unitPriceInput <= 0) {
                    $unitPriceInput = $sourceResolved['price'];
                }
            }

            if ($name === '') {
                continue;
            }

            $unitPrice = max(0, (float) ($unitPriceInput ?? 0));
            $lineTotal = $quantity * $unitPrice;

            $effectiveType = in_array($type, ['book_print', 'ebook', 'extra_service'], true)
                ? $type
                : 'book_print';

            $normalized[] = [
                'item_type' => $effectiveType,
                'publishing_package_id' => null,
                'product_source_type' => $sourceResolved['source_type'] ?? null,
                'product_source_id' => $sourceResolved['source_id'] ?? null,
                'item_name' => $name,
                'item_description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_type' => in_array((string) ($item['discount_type'] ?? ''), ['nominal', 'percent'], true)
                    ? (string) $item['discount_type']
                    : 'nominal',
                'discount_input' => max(0, (float) ($item['discount_amount'] ?? 0)),
                'discount_amount' => 0,
                'line_total_before_discount' => $lineTotal,
                'line_total' => $lineTotal,
                'extra_service_amount' => $effectiveType === 'extra_service' ? $lineTotal : 0,
            ];
        }

        return $normalized;
    }

    private function resolveProductSource(string $itemType, ?string $sourceType, ?int $sourceId): ?array
    {
        if (!$sourceType || !$sourceId) {
            return null;
        }

        if ($sourceType === 'store_catalog') {
            $catalog = StoreCatalogItem::query()->find($sourceId);
            if (!$catalog || !$catalog->is_active) {
                return null;
            }

            if ($itemType === 'ebook' && !$catalog->isEbook()) {
                return null;
            }

            if ($itemType === 'book_print' && !$catalog->isPrint()) {
                return null;
            }

            $format = $itemType === 'ebook' ? 'ebook' : 'print';

            return [
                'source_type' => 'store_catalog',
                'source_id' => $catalog->id,
                'name' => $catalog->title . ' (' . strtoupper($format) . ')',
                'price' => (float) $catalog->finalPriceForFormat($format),
            ];
        }

        if ($sourceType === 'legacy_book') {
            $legacy = LegacyBook::query()->find($sourceId);
            if (!$legacy) {
                return null;
            }

            if ($itemType === 'ebook' && !$legacy->distribution_ebook) {
                return null;
            }

            if ($itemType === 'book_print' && !$legacy->distribution_online) {
                return null;
            }

            return [
                'source_type' => 'legacy_book',
                'source_id' => $legacy->id,
                'name' => $legacy->title . ' (' . ($itemType === 'ebook' ? 'EBOOK' : 'PRINT') . ')',
                'price' => (float) ($legacy->list_price ?? 0),
            ];
        }

        if ($sourceType === 'book') {
            $book = Book::query()->find($sourceId);
            if (!$book) {
                return null;
            }

            if (!in_array((string) $book->workflow_status, ['isbn_approved', 'selesai'], true)) {
                return null;
            }

            if ($itemType === 'ebook' && empty($book->final_ebook_link)) {
                return null;
            }

            return [
                'source_type' => 'book',
                'source_id' => $book->id,
                'name' => $book->judul . ' (' . ($itemType === 'ebook' ? 'EBOOK' : 'PRINT') . ')',
                'price' => (float) ($book->selling_price ?? 0),
            ];
        }

        return null;
    }

    private function resolveLinkedUserId(?string $email, ?string $phone): ?int
    {
        if ($email) {
            $byEmail = User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            if ($byEmail) {
                return $byEmail->id;
            }
        }

        if ($phone) {
            $byPhone = User::query()->where('phone', $phone)->first();
            if ($byPhone) {
                return $byPhone->id;
            }
        }

        return null;
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'POS';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastNumber = PosOrder::where('order_number', 'like', "{$prefix}-{$year}{$month}%")
            ->lockForUpdate()
            ->count();

        $sequence = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    private function generateManuscriptNumber(): string
    {
        $prefix = 'MS-POS-' . now()->format('Ymd');
        $last = Book::query()->where('nomor_naskah', 'like', $prefix . '-%')->count();

        return $prefix . '-' . str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }
}
