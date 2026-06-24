<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\AuthorInvoice;
use App\Models\Book;
use App\Models\PrintPriceRule;
use App\Models\PublishingPackage;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class AuthorOrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $completedBooks = Book::where('author_user_id', $user->id)
            ->where('workflow_status', 'selesai')
            ->orderBy('judul')
            ->get();

        $packages = PublishingPackage::orderBy('name')->get();
        $printRules = PrintPriceRule::where('is_active', true)->orderBy('name')->get();

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
            'orders',
            'accumulatedPayments'
        ));
    }

    public function buyPackage(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAuthorProfileComplete()) {
            return back()->with('warning', 'Lengkapi data identitas penulis sebelum membeli paket baru.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'publishing_package_id' => ['required', 'exists:publishing_packages,id'],
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
        $invoice = AuthorInvoice::createPackageInvoice($book);

        $total = (float) (optional($book->publishingPackage)->price ?? 0);

        AuthorBookOrder::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'publishing_package_id' => $book->publishing_package_id,
            'author_invoice_id' => $invoice?->id,
            'order_type' => 'new_package',
            'title' => $book->judul,
            'quantity' => 1,
            'unit_price' => $total,
            'subtotal' => $total,
            'shipping_cost' => 0,
            'total_amount' => $total,
            'status' => $invoice ? 'invoiced' : 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Pembelian paket baru berhasil dibuat. Invoice DP 50% sudah diterbitkan.');
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
            ->findOrFail($data['book_id']);

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
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Pesanan cetak ulang berhasil dibuat. Invoice telah diterbitkan.');
    }
}
