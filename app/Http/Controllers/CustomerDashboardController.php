<?php

namespace App\Http\Controllers;

use App\Models\AuthorUpgradeRequest;
use App\Models\StoreOrder;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $ordersQuery = StoreOrder::query()->where('user_id', $user->id);

        $stats = [
            'total_orders' => (clone $ordersQuery)->count(),
            'pending_payment' => (clone $ordersQuery)->whereIn('status', ['pending', 'confirmed'])->count(),
            'paid_orders' => (clone $ordersQuery)->whereIn('status', ['paid', 'packed', 'shipped', 'completed'])->count(),
            'total_spent' => (float) (clone $ordersQuery)->whereIn('status', ['paid', 'packed', 'shipped', 'completed'])->sum('subtotal'),
        ];

        $recentOrders = (clone $ordersQuery)
            ->with('item')
            ->latest('id')
            ->limit(5)
            ->get();

        $latestUpgradeRequest = AuthorUpgradeRequest::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        return view('customer.dashboard', compact('stats', 'recentOrders', 'latestUpgradeRequest'));
    }
}
