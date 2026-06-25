@extends('adminlte::page')

@section('title', 'Store Orders')

@section('content_header')
    <h1 class="m-0">Order Storefront</h1>
@endsection

@section('content')
    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ $stats['confirmed'] }}</h3>
                    <p>Confirmed</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>{{ $stats['paid'] }}</h3>
                    <p>Paid</p>
                </div>
                <div class="icon"><i class="fas fa-credit-card"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['revenue_paid'], 0, ',', '.') }}</h3>
                    <p>Revenue Dibayar</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Daftar Order</strong>
            <form method="GET" class="form-inline" style="gap:.4rem;">
                <select name="status" class="form-control form-control-sm">
                    <option value="">Semua Status</option>
                    @foreach (['pending', 'confirmed', 'paid', 'packed', 'shipped', 'completed', 'cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ strtoupper($option) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Order</th>
                        <th>Produk</th>
                        <th>Pembeli</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th style="min-width:260px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <div class="small text-muted">{{ $order->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td>
                                {{ $order->item->title ?? '-' }}
                                <div class="small text-muted">Qty: {{ number_format($order->quantity) }}</div>
                            </td>
                            <td>
                                <div>{{ $order->customer_name }}</div>
                                <small class="text-muted">{{ $order->customer_phone }}</small>
                            </td>
                            <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            <td><span class="badge badge-light border">{{ strtoupper($order->status) }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('finance.store.orders.update', $order) }}"
                                    class="row">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-5 mb-1">
                                        <select name="status" class="form-control form-control-sm" required>
                                            @foreach (['pending', 'confirmed', 'paid', 'packed', 'shipped', 'completed', 'cancelled'] as $option)
                                                <option value="{{ $option }}" @selected($order->status === $option)>
                                                    {{ strtoupper($option) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-1">
                                        <input type="text" name="admin_notes" class="form-control form-control-sm"
                                            value="{{ $order->admin_notes }}" placeholder="Catatan admin">
                                    </div>
                                    <div class="col-md-2 mb-1">
                                        <button class="btn btn-xs btn-primary" type="submit">Simpan</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada order store.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
