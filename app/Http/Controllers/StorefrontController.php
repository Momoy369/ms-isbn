<?php

namespace App\Http\Controllers;

use App\Models\StoreCatalogItem;
use App\Models\StoreOrder;
use App\Models\User;
use App\Services\IpaymuService;
use App\Services\NotificationService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $items = StoreCatalogItem::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('author_name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('store.index', compact('items', 'search'));
    }

    public function show(string $slug)
    {
        $item = StoreCatalogItem::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $related = StoreCatalogItem::where('id', '!=', $item->id)
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('store.show', compact('item', 'related'));
    }

    public function placeOrder(Request $request, StoreCatalogItem $item, NotificationService $notifications, IpaymuService $ipaymu, RajaOngkirService $rajaOngkir)
    {
        if (!$item->is_active) {
            abort(404);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'shipping_address' => [$item->isEbook() ? 'nullable' : 'required', 'string', 'max:3000'],
            'shipping_destination_city_id' => [$item->isEbook() ? 'nullable' : 'required', 'string', 'max:32'],
            'shipping_courier' => [$item->isEbook() ? 'nullable' : 'required', 'in:jne,pos,tiki'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'reader_password' => [$item->isEbook() ? 'required' : 'nullable', 'string', 'min:6', 'max:64'],
        ]);

        $quantity = (int) $data['quantity'];

        if ($item->stock !== null && $quantity > (int) $item->stock) {
            return back()->with('warning', 'Jumlah pesanan melebihi stok yang tersedia.')->withInput();
        }

        $unitPrice = $item->finalPrice();
        $productSubtotal = $unitPrice * $quantity;

        $shippingCost = 0;
        $shippingService = null;
        $shippingEtd = null;

        if (!$item->isEbook()) {
            $estimate = $rajaOngkir->estimateCost(
                (string) $data['shipping_destination_city_id'],
                max(250, $quantity * 400),
                (string) $data['shipping_courier']
            );

            $shippingCost = (float) ($estimate['cost'] ?? 0);
            $shippingService = trim((string) (($estimate['service'] ?? '') . ' ' . ($estimate['description'] ?? '')));
            $shippingEtd = (string) ($estimate['etd'] ?? null);
        }

        $subtotal = $productSubtotal + $shippingCost;

        $order = StoreOrder::create([
            'user_id' => auth()->id(),
            'store_catalog_item_id' => $item->id,
            'order_number' => 'SO-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'shipping_address' => $data['shipping_address'] ?? null,
            'shipping_destination_city_id' => $data['shipping_destination_city_id'] ?? null,
            'shipping_courier' => $data['shipping_courier'] ?? null,
            'shipping_service' => $shippingService,
            'shipping_cost' => $shippingCost,
            'shipping_etd' => $shippingEtd,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'reader_password_hash' => $item->isEbook() ? Hash::make((string) $data['reader_password']) : null,
        ]);

        if ($item->stock !== null) {
            $item->decrement('stock', $quantity);
        }

        $financeUsers = User::whereIn('role', ['finance', 'owner', 'superadmin'])->get(['id']);
        foreach ($financeUsers as $user) {
            $notifications->send(
                $user->id,
                'Order Store Baru',
                'Order ' . $order->order_number . ' masuk untuk buku "' . $item->title . '" dengan total Rp ' . number_format($subtotal, 0, ',', '.') . '.',
                $item->book_id
            );
        }

        $checkout = $ipaymu->createStoreOrderCheckout($order);

        if (empty($checkout['checkout_url'])) {
            return back()->with('danger', 'Pesanan tercatat, namun pembuatan checkout iPaymu gagal. Silakan hubungi admin. Nomor order: ' . $order->order_number);
        }

        $order->update([
            'payment_method' => 'ipaymu',
            'payment_gateway' => 'ipaymu',
            'gateway_reference' => $checkout['reference'] ?? null,
            'gateway_checkout_url' => $checkout['checkout_url'],
            'gateway_expires_at' => $checkout['expires_at'] ?? null,
            'payment_reference' => $checkout['reference'] ?? $order->payment_reference,
        ]);

        return redirect()->away($checkout['checkout_url']);
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

        return view('store.reader', compact('order', 'ebookUrl'));
    }
}
