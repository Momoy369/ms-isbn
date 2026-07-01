@extends('adminlte::page')

@section('title', 'Dashboard Customer')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-shopping-bag mr-2"></i>Dashboard Customer</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-outline card-info mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
            <div class="mb-2 mb-md-0">
                <strong>Lanjut Belanja di Storefront</strong>
                <div class="text-muted small">Anda bisa kembali ke katalog toko kapan saja tanpa keluar dari akun customer.
                </div>
            </div>
            <div class="d-flex flex-wrap">
                <a href="{{ route('store.index') }}" class="btn btn-info mr-2 mb-2">
                    <i class="fas fa-store mr-1"></i> Buka Storefront
                </a>
                <a href="{{ route('store.track.form') }}" class="btn btn-outline-secondary mb-2">
                    <i class="fas fa-search mr-1"></i> Lacak Pesanan
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_orders'] }}</h3>
                    <p>Total Order</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending_payment'] }}</h3>
                    <p>Menunggu Pembayaran</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['paid_orders'] }}</h3>
                    <p>Order Terbayar</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</h3>
                    <p>Total Belanja</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit mr-1"></i>Status Upgrade Author</h3>
        </div>
        <div class="card-body">
            @if ($latestUpgradeRequest)
                <p class="mb-2">
                    Status pengajuan terakhir:
                    <span
                        class="badge badge-{{ $latestUpgradeRequest->status === 'approved' ? 'success' : ($latestUpgradeRequest->status === 'rejected' ? 'danger' : 'warning') }}">
                        {{ strtoupper($latestUpgradeRequest->status) }}
                    </span>
                </p>
                @if ($latestUpgradeRequest->review_notes)
                    <div class="alert alert-light border mb-0">
                        <strong>Catatan Reviewer:</strong><br>
                        {{ $latestUpgradeRequest->review_notes }}
                    </div>
                @endif
            @else
                <p class="text-muted mb-0">Belum ada pengajuan upgrade author. Lengkapi profil lalu ajukan di halaman
                    profile.</p>
            @endif
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-history mr-1"></i>Order Terbaru</h3>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Produk</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ optional($order->item)->title ?? '-' }}</td>
                            <td>
                                <span
                                    class="badge badge-{{ in_array($order->status, ['paid', 'packed', 'shipped', 'completed'], true) ? 'success' : (in_array($order->status, ['cancelled'], true) ? 'danger' : 'warning') }}">
                                    {{ strtoupper($order->status) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('customer.orders.show', $order) }}"
                                    class="btn btn-xs btn-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
