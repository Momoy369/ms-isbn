<?php

namespace App\Http\Controllers;

use App\Models\StoreOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerEbookLibraryController extends Controller
{
    public function index()
    {
        $ebookOrders = StoreOrder::query()
            ->with('item')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['paid', 'completed'])
            ->whereNotNull('reader_password_hash')
            ->orderByDesc('paid_at')
            ->latest('id')
            ->paginate(15);

        return view('customer.ebooks.index', compact('ebookOrders'));
    }

    public function open(Request $request, StoreOrder $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if(!$order->item || !$order->item->isEbook(), 404);

        if (!in_array($order->status, ['paid', 'completed'], true)) {
            return back()->with('warning', 'Akses ebook tersedia setelah pembayaran terkonfirmasi.');
        }

        if (!$order->reader_password_hash) {
            return back()->with('warning', 'Password baca ebook belum tersedia.');
        }

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check((string) $data['password'], $order->reader_password_hash)) {
            return back()->with('danger', 'Password baca ebook tidak valid.');
        }

        $currentSessionId = (string) $request->session()->getId();
        $currentDeviceHash = hash('sha256', (string) ($request->userAgent() . '|' . $request->ip()));
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
}
