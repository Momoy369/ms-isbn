@extends('adminlte::page')

@section('title', 'Riwayat Order Customer')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-receipt mr-2"></i>Riwayat Order dan Invoice Store</h1>
@stop

@section('content')
    <div class="card card-outline card-info mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.orders.index') }}" class="row">
                <div class="col-md-4 mb-2">
                    <label class="mb-1">Cari</label>
                    <input type="text" name="q" value="{{ $keyword }}" class="form-control"
                        placeholder="Nomor order / payment ref / judul produk">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        @foreach (['pending', 'confirmed', 'paid', 'packed', 'shipped', 'completed', 'cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                                {{ strtoupper($statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 mb-2 d-flex align-items-end justify-content-end">
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-light border mr-2">Reset</a>
                    <button type="submit" class="btn btn-info">Terapkan</button>
                </div>
            </form>

            <div class="mt-3 d-flex flex-wrap">
                <span class="badge badge-warning mr-2 mb-2 px-3 py-2">Pending: {{ $summary['pending'] }}</span>
                <span class="badge badge-success mr-2 mb-2 px-3 py-2">Paid+: {{ $summary['paid'] }}</span>
                <span class="badge badge-danger mr-2 mb-2 px-3 py-2">Cancelled: {{ $summary['cancelled'] }}</span>
                <span class="badge badge-primary mb-2 px-3 py-2">Total Belanja: Rp
                    {{ number_format($summary['total_spent'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Invoice/Ref</th>
                        <th>Produk</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->payment_reference ?? ($order->gateway_reference ?? '-') }}</td>
                            <td>{{ optional($order->item)->title ?? '-' }}</td>
                            <td>
                                <span
                                    class="badge badge-{{ in_array($order->status, ['paid', 'packed', 'shipped', 'completed'], true) ? 'success' : (in_array($order->status, ['cancelled'], true) ? 'danger' : 'warning') }}">
                                    {{ strtoupper($order->status) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                            <td>{{ optional($order->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}"
                                    class="btn btn-xs btn-primary">Detail</a>
                                @if (in_array($order->status, ['pending', 'confirmed'], true) && $order->gateway_checkout_url)
                                    <a href="{{ $order->gateway_checkout_url }}" class="btn btn-xs btn-success"
                                        target="_blank" rel="noopener">Bayar</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada data order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
