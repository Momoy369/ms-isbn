<?php

namespace App\Http\Controllers;

use App\Models\PublishingPackage;
use App\Models\AdditionalService;
use App\Models\StorePackageConsultation;
use App\Models\StoreCatalogItem;
use App\Models\StoreOrder;
use App\Models\StoreVoucher;
use App\Models\User;
use App\Services\IpaymuService;
use App\Services\NotificationService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $productType = trim((string) $request->query('type', 'all'));
        $sort = trim((string) $request->query('sort', 'featured'));
        $onlyFeatured = $request->boolean('featured');
        $onlyPromo = $request->boolean('promo');
        $minPriceInput = trim((string) $request->query('min_price', ''));
        $maxPriceInput = trim((string) $request->query('max_price', ''));

        $minPrice = is_numeric($minPriceInput) ? max(0, (float) $minPriceInput) : null;
        $maxPrice = is_numeric($maxPriceInput) ? max(0, (float) $maxPriceInput) : null;

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
            [$minPriceInput, $maxPriceInput] = [$maxPriceInput, $minPriceInput];
        }

        $allowedTypes = ['all', 'print', 'ebook', 'print_ebook'];
        if (!in_array($productType, $allowedTypes, true)) {
            $productType = 'all';
        }

        $allowedSorts = ['featured', 'newest', 'price_low', 'price_high', 'title_asc'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'featured';
        }

        $baseQuery = StoreCatalogItem::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('author_name', 'like', '%' . $search . '%');
                });
            })
            ->when($minPrice !== null, fn($query) => $query->where('list_price', '>=', $minPrice))
            ->when($maxPrice !== null, fn($query) => $query->where('list_price', '<=', $maxPrice))
            ->when($onlyFeatured, fn($query) => $query->where('is_featured', true))
            ->when($onlyPromo, fn($query) => $query->where(function ($q) {
                $q->whereNotNull('promo_price')
                    ->orWhereNotNull('ebook_promo_price');
            }))
            ->when($productType !== 'all', fn($query) => $query->where('product_type', $productType));

        $items = (clone $baseQuery)
            ->when($sort === 'featured', function ($query) {
                $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id');
            })
            ->when($sort === 'newest', fn($query) => $query->orderByDesc('id'))
            ->when($sort === 'price_low', fn($query) => $query->orderBy('list_price')->orderByDesc('id'))
            ->when($sort === 'price_high', fn($query) => $query->orderByDesc('list_price')->orderByDesc('id'))
            ->when($sort === 'title_asc', fn($query) => $query->orderBy('title')->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'featured' => (clone $baseQuery)->where('is_featured', true)->count(),
            'ebook_ready' => (clone $baseQuery)->whereIn('product_type', ['ebook', 'print_ebook'])->count(),
        ];

        $packages = PublishingPackage::query()
            ->orderBy('price')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $hasActiveFilters = $search !== ''
            || $productType !== 'all'
            || $sort !== 'featured'
            || $onlyFeatured
            || $onlyPromo
            || $minPrice !== null
            || $maxPrice !== null;

        return view('store.index', compact(
            'items',
            'search',
            'productType',
            'sort',
            'stats',
            'packages',
            'onlyFeatured',
            'onlyPromo',
            'minPriceInput',
            'maxPriceInput',
            'hasActiveFilters'
        ));
    }

    public function show(string $slug, RajaOngkirService $rajaOngkir)
    {
        $item = StoreCatalogItem::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $soldStatuses = ['paid', 'confirmed', 'shipped', 'completed'];

        $relatedAuthor = StoreCatalogItem::query()
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->withSum([
                'orders as sold_quantity' => fn($query) => $query->whereIn('status', $soldStatuses),
            ], 'quantity')
            ->when(
                !empty($item->author_name),
                fn($query) => $query->where('author_name', $item->author_name)
            )
            ->orderByDesc('sold_quantity')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $relatedType = StoreCatalogItem::query()
            ->where('id', '!=', $item->id)
            ->where('is_active', true)
            ->withSum([
                'orders as sold_quantity' => fn($query) => $query->whereIn('status', $soldStatuses),
            ], 'quantity')
            ->where('product_type', $item->product_type)
            ->whereNotIn('id', $relatedAuthor->pluck('id'))
            ->orderByDesc('sold_quantity')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $provinceMeta = $rajaOngkir->provincesWithMeta();
        $shippingProvinces = $provinceMeta['data'] ?? [];
        $shippingMeta = [
            'is_fallback' => (bool) ($provinceMeta['is_fallback'] ?? false),
            'message' => $provinceMeta['message'] ?? null,
        ];

        return view('store.show', compact('item', 'relatedAuthor', 'relatedType', 'shippingProvinces', 'shippingMeta'));
    }

    public function shippingCities(Request $request, RajaOngkirService $rajaOngkir)
    {
        $data = $request->validate([
            'province_id' => ['required', 'string', 'max:32'],
        ]);

        $cityMeta = $rajaOngkir->citiesWithMeta((string) $data['province_id']);

        return response()->json([
            'data' => $cityMeta['data'] ?? [],
            'is_fallback' => (bool) ($cityMeta['is_fallback'] ?? false),
            'message' => $cityMeta['message'] ?? null,
        ]);
    }

    public function packageConfigurator(Request $request)
    {
        $packages = PublishingPackage::query()
            ->orderBy('price')
            ->orderBy('name')
            ->get();

        $services = AdditionalService::query()
            ->where('is_active', true)
            ->orderBy('service_type')
            ->orderBy('name')
            ->get(['id', 'name', 'service_type', 'description', 'price']);

        $selectedPackageId = (int) $request->query('package_id', old('publishing_package_id', 0));
        $selectedPackage = $packages->firstWhere('id', $selectedPackageId);

        return view('store.package-configurator', [
            'packages' => $packages,
            'selectedPackage' => $selectedPackage,
            'services' => $services,
            'budgetOptions' => [
                '< 3 juta',
                '3 - 5 juta',
                '5 - 10 juta',
                '> 10 juta',
            ],
        ]);
    }

    public function submitPackageConfigurator(Request $request, NotificationService $notifications)
    {
        $activeServiceIds = AdditionalService::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'publishing_package_id' => ['required', 'integer', 'exists:publishing_packages,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'manuscript_title' => ['nullable', 'string', 'max:190'],
            'manuscript_genre' => ['nullable', 'string', 'max:120'],
            'estimated_page_count' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'target_publish_date' => ['nullable', 'date', 'after:today'],
            'budget_range' => ['nullable', 'string', 'max:64'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $selectedServiceIds = collect($data['services'] ?? [])
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => in_array($id, $activeServiceIds, true))
            ->unique()
            ->values()
            ->all();

        $package = PublishingPackage::query()->findOrFail((int) $data['publishing_package_id']);

        $selectedServices = AdditionalService::query()
            ->whereIn('id', $selectedServiceIds)
            ->get(['id', 'name', 'price']);

        $estimate = $this->calculatePackageEstimate(
            $package,
            $selectedServices,
            isset($data['estimated_page_count']) ? (int) $data['estimated_page_count'] : null
        );

        $selectedServicesPayload = $selectedServices
            ->map(fn($service) => [
                'id' => (int) $service->id,
                'name' => (string) $service->name,
                'price' => (float) $service->price,
            ])
            ->values()
            ->all();

        $consultation = StorePackageConsultation::create([
            'user_id' => auth()->id(),
            'publishing_package_id' => $package->id,
            'package_name' => (string) $package->name,
            'package_base_price' => (float) $package->price,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'manuscript_title' => $data['manuscript_title'] ?? null,
            'manuscript_genre' => $data['manuscript_genre'] ?? null,
            'estimated_page_count' => $data['estimated_page_count'] ?? null,
            'target_publish_date' => $data['target_publish_date'] ?? null,
            'budget_range' => $data['budget_range'] ?? null,
            'selected_services' => $selectedServicesPayload,
            'estimated_total' => $estimate['total'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'source' => 'storefront-configurator',
        ]);

        $notifyUsers = User::query()
            ->whereIn('role', ['admin', 'finance', 'owner', 'superadmin'])
            ->get(['id']);

        foreach ($notifyUsers as $user) {
            $notifications->send(
                (int) $user->id,
                'Lead Paket Penerbitan Baru',
                'Lead konsultasi baru untuk paket ' . $package->name . ' atas nama ' . $consultation->customer_name
                . ' (estimasi Rp ' . number_format((float) $consultation->estimated_total, 0, ',', '.') . ').',
                null
            );
        }

        return redirect()
            ->route('store.package-configurator', ['package_id' => $package->id])
            ->with('success', 'Permintaan konsultasi berhasil dikirim. Tim kami akan menghubungi Anda untuk tahap berikutnya.');
    }

    private function calculatePackageEstimate(PublishingPackage $package, $services, ?int $pageCount): array
    {
        $base = (float) $package->price;
        $serviceTotal = collect($services)
            ->map(fn($service) => (float) ($service->price ?? 0))
            ->sum();

        $pageSurcharge = 0;
        if ($pageCount !== null && $pageCount > 125) {
            $extraPages = $pageCount - 125;
            $pageSurcharge = $extraPages * 1500;
        }

        return [
            'base' => $base,
            'service_total' => $serviceTotal,
            'page_surcharge' => $pageSurcharge,
            'total' => max(0, $base + $serviceTotal + $pageSurcharge),
        ];
    }

    public function placeOrder(Request $request, StoreCatalogItem $item, NotificationService $notifications, IpaymuService $ipaymu, RajaOngkirService $rajaOngkir)
    {
        if (!$item->is_active) {
            abort(404);
        }

        $needsShipping = $item->isPrint();
        $hasEbookAccess = $item->isEbook();

        // Untuk produk print+ebook, customer wajib memilih salah satu format.
        $selectedFormat = null;
        if ($item->hasSeparateFormats()) {
            $selectedFormat = $request->input('selected_format');
            if (!in_array($selectedFormat, ['print', 'ebook'], true)) {
                return back()->with('warning', 'Pilih format pembelian: Print atau Ebook.')->withInput();
            }
            $needsShipping = $selectedFormat === 'print';
            $hasEbookAccess = $selectedFormat === 'ebook';
        }

        $isPrintPurchase = $item->hasSeparateFormats()
            ? $selectedFormat === 'print'
            : $item->product_type === 'print';

        $quantityMax = $isPrintPurchase ? 1000 : 100000;

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $quantityMax],
            'selected_format' => [$item->hasSeparateFormats() ? 'required' : 'nullable', 'in:print,ebook'],
            'shipping_destination_province_id' => [$needsShipping ? 'required' : 'nullable', 'string', 'max:32'],
            'shipping_destination_province_name' => [$needsShipping ? 'required' : 'nullable', 'string', 'max:120'],
            'shipping_address' => [$needsShipping ? 'required' : 'nullable', 'string', 'max:3000'],
            'shipping_destination_city_id' => [$needsShipping ? 'required' : 'nullable', 'string', 'max:32'],
            'shipping_destination_city_name' => [$needsShipping ? 'required' : 'nullable', 'string', 'max:120'],
            'shipping_courier' => [$needsShipping ? 'required' : 'nullable', 'in:jne,pos,tiki'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reader_password' => [$hasEbookAccess ? 'required' : 'nullable', 'string', 'min:6', 'max:64'],
            'voucher_code' => ['nullable', 'string', 'max:64'],
        ]);

        $quantity = (int) $data['quantity'];

        if ($isPrintPurchase && $item->stock !== null && $quantity > (int) $item->stock) {
            return back()->with('warning', 'Jumlah pesanan melebihi stok yang tersedia.')->withInput();
        }

        $voucherCode = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
        $voucher = null;

        $unitPrice = $item->hasSeparateFormats() && $selectedFormat
            ? $item->finalPriceForFormat($selectedFormat)
            : $item->finalPrice();
        $productSubtotal = $unitPrice * $quantity;

        if ($voucherCode !== '') {
            $voucher = StoreVoucher::query()
                ->whereRaw('UPPER(code) = ?', [$voucherCode])
                ->first();

            if (!$voucher || !$voucher->isCurrentlyActive()) {
                return back()->with('warning', 'Kode voucher tidak valid atau sudah tidak aktif.')->withInput();
            }

            if (!$voucher->appliesToItem($item)) {
                return back()->with('warning', 'Voucher ini tidak berlaku untuk format produk yang dipilih.')->withInput();
            }

            if (!$voucher->canApplyToSubtotal($productSubtotal)) {
                return back()->with('warning', 'Subtotal belum memenuhi minimum penggunaan voucher.')->withInput();
            }
        }

        $shippingCost = 0;
        $shippingService = null;
        $shippingEtd = null;

        if ($needsShipping) {
            $estimate = $rajaOngkir->estimateCost(
                (string) $data['shipping_destination_city_id'],
                max(250, $quantity * 400),
                (string) $data['shipping_courier']
            );

            $shippingCost = (float) ($estimate['cost'] ?? 0);
            $shippingService = trim((string) (($estimate['service'] ?? '') . ' ' . ($estimate['description'] ?? '')));
            $shippingEtd = (string) ($estimate['etd'] ?? null);
        }

        $voucherDiscount = $voucher ? $voucher->calculateDiscount($productSubtotal) : 0.0;
        $subtotalBeforeDiscount = $productSubtotal + $shippingCost;
        $subtotal = max(0, $subtotalBeforeDiscount - $voucherDiscount);

        try {
            $checkoutUrl = DB::transaction(function () use ($item, $data, $selectedFormat, $hasEbookAccess, $quantity, $unitPrice, $subtotal, $subtotalBeforeDiscount, $shippingCost, $shippingService, $shippingEtd, $voucher, $voucherCode, $voucherDiscount, $notifications, $ipaymu) {
                $order = StoreOrder::create([
                    'user_id' => auth()->id(),
                    'store_catalog_item_id' => $item->id,
                    'voucher_id' => $voucher?->id,
                    'voucher_code' => $voucherCode !== '' ? $voucherCode : null,
                    'voucher_name' => $voucher?->name,
                    'selected_format' => $selectedFormat ?? ($item->product_type === 'ebook' ? 'ebook' : 'print'),
                    'order_number' => 'SO-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'customer_email' => $data['customer_email'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal_before_discount' => $subtotalBeforeDiscount,
                    'voucher_discount_amount' => $voucherDiscount,
                    'subtotal' => $subtotal,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'shipping_destination_province_id' => $data['shipping_destination_province_id'] ?? null,
                    'shipping_destination_province_name' => $data['shipping_destination_province_name'] ?? null,
                    'shipping_destination_city_id' => $data['shipping_destination_city_id'] ?? null,
                    'shipping_destination_city_name' => $data['shipping_destination_city_name'] ?? null,
                    'shipping_courier' => $data['shipping_courier'] ?? null,
                    'shipping_service' => $shippingService,
                    'shipping_cost' => $shippingCost,
                    'shipping_etd' => $shippingEtd,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'pending',
                    'reader_password_hash' => $hasEbookAccess ? Hash::make((string) $data['reader_password']) : null,
                ]);

                if ($voucher) {
                    $voucher->increment('used_count');
                }

                if ($item->isPrint() && $item->stock !== null) {
                    $item->decrement('stock', $quantity);
                }

                $financeUsers = User::whereIn('role', ['finance', 'owner', 'superadmin'])->get(['id']);
                foreach ($financeUsers as $user) {
                    $voucherInfo = $voucher ? ' Voucher ' . $voucher->code . ' dipakai, diskon Rp ' . number_format($voucherDiscount, 0, ',', '.') . '.' : '.';
                    $notifications->send(
                        $user->id,
                        'Order Store Baru',
                        'Order ' . $order->order_number . ' masuk untuk buku "' . $item->title . '" dengan total Rp ' . number_format($subtotal, 0, ',', '.') . $voucherInfo,
                        $item->book_id
                    );
                }

                $checkout = $ipaymu->createStoreOrderCheckout($order);

                if (empty($checkout['checkout_url'])) {
                    throw new \RuntimeException('Pesanan tercatat, namun pembuatan checkout iPaymu gagal.');
                }

                $order->update([
                    'payment_method' => 'ipaymu',
                    'payment_gateway' => 'ipaymu',
                    'gateway_reference' => $checkout['reference'] ?? null,
                    'gateway_checkout_url' => $checkout['checkout_url'],
                    'gateway_expires_at' => $checkout['expires_at'] ?? null,
                    'payment_reference' => $checkout['reference'] ?? $order->payment_reference,
                ]);

                return $checkout['checkout_url'];
            });
        } catch (\Throwable $e) {
            return back()->with('danger', $e->getMessage())->withInput();
        }

        return redirect()->away($checkoutUrl);
    }

    public function trackForm()
    {
        return view('store.track-form');
    }

    public function trackLookup(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:64'],
        ]);

        $order = StoreOrder::where('order_number', strtoupper(trim((string) $data['order_number'])))->first();

        if (!$order) {
            return back()->with('danger', 'Nomor order tidak ditemukan.');
        }

        return redirect()->route('store.track.show', ['orderNumber' => $order->order_number]);
    }

    public function trackShow(string $orderNumber)
    {
        $order = StoreOrder::with('item')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('store.track-show', compact('order'));
    }

    public function reader(Request $request, string $orderNumber)
    {
        $order = StoreOrder::with('item')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!$order->item || !$order->item->isEbook()) {
            abort(404);
        }

        if ($order->status !== 'paid' && $order->status !== 'completed') {
            return back()->with('warning', 'Akses ebook tersedia setelah pembayaran terkonfirmasi.');
        }

        $currentSessionId = (string) $request->session()->getId();
        $currentDeviceHash = hash('sha256', (string) ($request->userAgent() . '|' . $request->ip()));

        if (!empty($order->reader_last_device_hash) && !hash_equals((string) $order->reader_last_device_hash, $currentDeviceHash)) {
            return back()->with('danger', 'Akses ebook dibatasi untuk satu perangkat terdaftar. Hubungi admin jika perlu reset akses.');
        }

        if (!empty($order->reader_last_session_id) && (string) $order->reader_last_session_id !== $currentSessionId) {
            return back()->with('warning', 'Sesi ebook sudah aktif di perangkat/sesi lain. Tutup sesi lama atau hubungi admin untuk reset.');
        }

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!$order->reader_password_hash || !Hash::check((string) $data['password'], $order->reader_password_hash)) {
            return back()->with('danger', 'Password baca ebook tidak valid.');
        }

        $ebookUrl = $order->item->ebook_read_link;
        if (!$ebookUrl) {
            return back()->with('warning', 'Ebook belum dipublikasikan. Hubungi admin.');
        }

        $plainToken = Str::random(72);
        $tokenHash = hash('sha256', $plainToken);

        $order->update([
            'reader_access_token_hash' => $tokenHash,
            'reader_access_token_expires_at' => now()->addMinutes(10),
            'reader_last_device_hash' => $currentDeviceHash,
            'reader_last_session_id' => $currentSessionId,
            'reader_active_sessions' => 1,
            'reader_last_used_at' => now(),
        ]);

        return redirect()->route('store.reader.view', [
            'orderNumber' => $order->order_number,
            'token' => $plainToken,
        ]);
    }

    public function readerView(Request $request, string $orderNumber)
    {
        $order = StoreOrder::with('item')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (!$order->item || !$order->item->isEbook()) {
            abort(404);
        }

        if ($order->status !== 'paid' && $order->status !== 'completed') {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('warning', 'Akses ebook tersedia setelah pembayaran terkonfirmasi.');
        }

        $token = (string) $request->query('token', '');
        if ($token === '') {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('danger', 'Token akses ebook tidak ditemukan.');
        }

        $tokenHash = hash('sha256', $token);

        if (empty($order->reader_access_token_hash) || !hash_equals((string) $order->reader_access_token_hash, $tokenHash)) {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('danger', 'Token akses ebook tidak valid atau sudah digunakan.');
        }

        if (!$order->reader_access_token_expires_at || now()->greaterThan($order->reader_access_token_expires_at)) {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('warning', 'Token akses ebook sudah kedaluwarsa. Silakan login ulang dengan password baca.');
        }

        $currentSessionId = (string) $request->session()->getId();
        $currentDeviceHash = hash('sha256', (string) ($request->userAgent() . '|' . $request->ip()));

        if (!empty($order->reader_last_device_hash) && !hash_equals((string) $order->reader_last_device_hash, $currentDeviceHash)) {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('danger', 'Perangkat ini tidak terdaftar untuk akses ebook order ini.');
        }

        if (!empty($order->reader_last_session_id) && (string) $order->reader_last_session_id !== $currentSessionId) {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('warning', 'Sesi ebook aktif di sesi lain.');
        }

        $ebookUrl = $order->item->ebook_read_link;
        if (!$ebookUrl) {
            return redirect()
                ->route('store.track.show', $order->order_number)
                ->with('warning', 'Ebook belum dipublikasikan. Hubungi admin.');
        }

        $order->update([
            'reader_access_token_hash' => null,
            'reader_access_token_expires_at' => null,
            'reader_access_granted_at' => now(),
            'reader_last_used_at' => now(),
            'reader_active_sessions' => 1,
        ]);

        $watermarkText = 'MS Publishing • ' . $order->order_number;

        return view('store.reader', compact('order', 'ebookUrl', 'watermarkText'));
    }
}
