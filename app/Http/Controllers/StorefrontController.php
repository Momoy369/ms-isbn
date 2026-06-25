<?php

namespace App\Http\Controllers;

use App\Models\StoreCatalogItem;
use App\Models\StoreOrder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

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

    public function placeOrder(Request $request, StoreCatalogItem $item, NotificationService $notifications)
    {
        if (!$item->is_active) {
            abort(404);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'shipping_address' => ['nullable', 'string', 'max:3000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quantity = (int) $data['quantity'];

        if ($item->stock !== null && $quantity > (int) $item->stock) {
            return back()->with('warning', 'Jumlah pesanan melebihi stok yang tersedia.')->withInput();
        }

        $unitPrice = $item->finalPrice();
        $subtotal = $unitPrice * $quantity;

        $order = StoreOrder::create([
            'store_catalog_item_id' => $item->id,
            'order_number' => 'SO-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'shipping_address' => $data['shipping_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
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

        return back()->with('success', 'Pesanan berhasil dikirim. Tim kami akan segera menghubungi Anda. Nomor order: ' . $order->order_number);
    }
}
