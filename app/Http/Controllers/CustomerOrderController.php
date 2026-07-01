<?php

namespace App\Http\Controllers;

use App\Models\StoreOrder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $keyword = trim((string) $request->query('q', ''));

        $orders = StoreOrder::query()
            ->with('item')
            ->where('user_id', auth()->id())
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($inner) use ($keyword): void {
                    $inner->where('order_number', 'like', "%{$keyword}%")
                        ->orWhere('payment_reference', 'like', "%{$keyword}%")
                        ->orWhereHas('item', fn($itemQuery) => $itemQuery->where('title', 'like', "%{$keyword}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => StoreOrder::where('user_id', auth()->id())->whereIn('status', ['pending', 'confirmed'])->count(),
            'paid' => StoreOrder::where('user_id', auth()->id())->whereIn('status', ['paid', 'packed', 'shipped', 'completed'])->count(),
            'cancelled' => StoreOrder::where('user_id', auth()->id())->where('status', 'cancelled')->count(),
            'total_spent' => (float) StoreOrder::where('user_id', auth()->id())
                ->whereIn('status', ['paid', 'packed', 'shipped', 'completed'])
                ->sum('subtotal'),
        ];

        return view('customer.orders.index', compact('orders', 'summary', 'status', 'keyword'));
    }

    public function show(StoreOrder $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $order->load('item');

        return view('customer.orders.show', compact('order'));
    }

    public function requestRefund(Request $request, StoreOrder $order, NotificationService $notifications)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (!in_array($order->status, ['paid', 'packed', 'shipped', 'completed'], true)) {
            return back()->with('warning', 'Refund hanya dapat diajukan untuk order yang sudah dibayar.');
        }

        if ($order->refund_status === 'requested') {
            return back()->with('warning', 'Refund untuk order ini sudah diajukan dan sedang menunggu review.');
        }

        $data = $request->validate([
            'refund_reason' => ['required', 'string', 'max:2000'],
        ]);

        $order->update([
            'refund_status' => 'requested',
            'refund_reason' => $data['refund_reason'],
            'refund_requested_at' => now(),
            'refund_notes' => null,
        ]);

        $financeUsers = User::whereIn('role', ['finance', 'owner', 'superadmin'])->get(['id']);
        foreach ($financeUsers as $user) {
            $notifications->send(
                (int) $user->id,
                'Permintaan Refund Order Store',
                'Order ' . $order->order_number . ' mengajukan refund dan menunggu review finance.',
                optional($order->item)->book_id
            );
        }

        return back()->with('success', 'Permintaan refund sudah dikirim.');
    }
}
