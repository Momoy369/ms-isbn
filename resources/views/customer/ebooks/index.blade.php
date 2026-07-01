@extends('adminlte::page')

@section('title', 'Library Ebook')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-book-open mr-2"></i>Library Ebook</h1>
@stop

@section('content')
    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="card card-outline card-info mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
            <div class="mb-2 mb-md-0">
                <strong>Koleksi Ebook yang Sudah Dibeli</strong>
                <div class="text-muted small">Daftar ini hanya menampilkan order ebook yang sudah aktif dan siap dibaca.
                </div>
                <div class="text-muted small">Masukkan password baca untuk membuka reader pada order yang sesuai.</div>
            </div>
            <div class="d-flex flex-wrap">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-primary mr-2 mb-2">
                    <i class="fas fa-file-invoice mr-1"></i> Riwayat Order
                </a>
                <a href="{{ route('store.index') }}" class="btn btn-info mb-2">
                    <i class="fas fa-store mr-1"></i> Storefront
                </a>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Buku</th>
                        <th>Tanggal Beli</th>
                        <th>Status</th>
                        <th>Buka Ebook</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ebookOrders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <div class="small text-muted">{{ strtoupper($order->selected_format ?? 'ebook') }}</div>
                                <span class="badge badge-success mt-1">Siap Dibaca</span>
                            </td>
                            <td>
                                <div>{{ optional($order->item)->title ?? '-' }}</div>
                                <div class="small text-muted">{{ optional($order->item)->author_name ?? '-' }}</div>
                            </td>
                            <td>{{ optional($order->paid_at ?? $order->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <span class="badge badge-success">{{ strtoupper($order->status) }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('customer.ebooks.open', $order) }}"
                                    class="form-inline" style="gap:.4rem;">
                                    @csrf
                                    <input type="password" name="password" class="form-control form-control-sm"
                                        placeholder="Password baca" autocomplete="off" required>
                                    <button class="btn btn-sm btn-primary" type="submit">
                                        <i class="fas fa-book-reader mr-1"></i>Buka Reader
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <div class="mb-2">Belum ada ebook yang bisa dibuka.</div>
                                <a href="{{ route('store.index') }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-store mr-1"></i> Ke Storefront
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ebookOrders->hasPages())
            <div class="card-footer">{{ $ebookOrders->links() }}</div>
        @endif
    </div>
@endsection
