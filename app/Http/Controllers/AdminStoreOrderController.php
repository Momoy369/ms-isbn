<?php

namespace App\Http\Controllers;

use App\Models\StoreOrder;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminStoreOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $orders = StoreOrder::with('item')
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'pending' => (int) StoreOrder::where('status', 'pending')->count(),
            'confirmed' => (int) StoreOrder::where('status', 'confirmed')->count(),
            'paid' => (int) StoreOrder::where('status', 'paid')->count(),
            'completed' => (int) StoreOrder::where('status', 'completed')->count(),
            'revenue_paid' => (float) StoreOrder::whereIn('status', ['paid', 'packed', 'shipped', 'completed'])->sum('subtotal'),
        ];

        return view('finance.store.orders', compact('orders', 'stats', 'status'));
    }

    public function update(Request $request, StoreOrder $order, NotificationService $notifications)
    {
        $previousStatus = (string) $order->status;

        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,paid,packed,shipped,completed,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'shipping_courier' => ['nullable', 'string', 'max:64'],
        ]);

        $newStatus = (string) $data['status'];

        if ($newStatus !== $previousStatus && !$this->canTransitionStatus($previousStatus, $newStatus)) {
            return back()->with('warning', 'Transisi status dari ' . strtoupper($previousStatus) . ' ke ' . strtoupper($newStatus) . ' tidak diizinkan.')->withInput();
        }

        $payload = [
            'status' => $newStatus,
            'admin_notes' => $data['admin_notes'] ?? $order->admin_notes,
            'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
            'shipping_courier' => $data['shipping_courier'] ?? $order->shipping_courier,
        ];

        if ($newStatus === 'confirmed' && !$order->confirmed_at) {
            $payload['confirmed_at'] = now();
        }

        if ($newStatus === 'completed' && !$order->completed_at) {
            $payload['completed_at'] = now();
        }

        if ($newStatus === 'shipped' && !$order->shipped_at) {
            $payload['shipped_at'] = now();
        }

        if ($newStatus === 'paid' && !$order->paid_at) {
            $payload['paid_at'] = now();
        }

        $order->update($payload);

        if ($newStatus !== $previousStatus && $order->user_id) {
            $notifications->send(
                (int) $order->user_id,
                'Status Order Store Diperbarui',
                'Order ' . $order->order_number . ' berubah dari ' . strtoupper($previousStatus) . ' menjadi ' . strtoupper($newStatus) . '.',
                optional($order->item)->book_id
            );
        }

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    private function canTransitionStatus(string $currentStatus, string $nextStatus): bool
    {
        $transitions = [
            'pending' => ['confirmed', 'paid', 'cancelled'],
            'confirmed' => ['paid', 'cancelled'],
            'paid' => ['packed'],
            'packed' => ['shipped'],
            'shipped' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        return in_array($nextStatus, $transitions[$currentStatus] ?? [], true);
    }
}
