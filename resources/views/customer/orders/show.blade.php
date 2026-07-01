@extends('adminlte::page')

@section('title', 'Detail Order Customer')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-file-invoice mr-2"></i>Detail Order {{ $order->order_number }}</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Ringkasan Order / Invoice Store</h3>
            <div>
                <a href="{{ route('store.index') }}" class="btn btn-sm btn-info mr-1">
                    <i class="fas fa-store mr-1"></i> Storefront
                </a>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-light border">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nomor Order:</strong> {{ $order->order_number }}</p>
                    <p><strong>Produk:</strong> {{ optional($order->item)->title ?? '-' }}</p>
                    <p><strong>Status:</strong> {{ strtoupper($order->status) }}</p>
                    @if ($order->subtotal_before_discount && $order->subtotal_before_discount > $order->subtotal)
                        <p class="mb-1"><strong>Subtotal Awal:</strong>
                            Rp {{ number_format((float) $order->subtotal_before_discount, 0, ',', '.') }}</p>
                        <p class="mb-1 text-success"><strong>Diskon Voucher:</strong>
                            -Rp {{ number_format((float) $order->voucher_discount_amount, 0, ',', '.') }}</p>
                    @endif
                    <p><strong>Total Bayar:</strong> Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</p>
                    @if ($order->voucher_code)
                        <p class="mb-1"><strong>Voucher:</strong> {{ $order->voucher_code }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>Payment Reference:</strong> {{ $order->payment_reference ?? '-' }}</p>
                    <p><strong>Gateway Reference:</strong> {{ $order->gateway_reference ?? '-' }}</p>
                    <p><strong>Dibuat:</strong> {{ optional($order->created_at)->format('d M Y H:i') }}</p>
                    <p><strong>Dibayar:</strong> {{ optional($order->paid_at)->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>

            @if (in_array($order->refund_status, ['requested', 'approved', 'rejected'], true))
                <hr>
                <div
                    class="alert alert-{{ $order->refund_status === 'approved' ? 'success' : ($order->refund_status === 'rejected' ? 'danger' : 'warning') }} mb-0">
                    <h5 class="mb-2">Status Refund</h5>
                    <p class="mb-2">
                        <span class="badge badge-light border">
                            {{ strtoupper($order->refund_status) }}
                        </span>
                    </p>
                    @if ($order->refund_reason)
                        <p class="mb-1"><strong>Alasan:</strong> {{ $order->refund_reason }}</p>
                    @endif
                    @if ($order->refund_notes)
                        <p class="mb-0"><strong>Catatan Finance:</strong> {{ $order->refund_notes }}</p>
                    @endif
                </div>
            @endif

            @if (in_array($order->status, ['paid', 'packed', 'shipped', 'completed'], true) && $order->refund_status !== 'requested')
                <hr>
                <h5>Ajukan Refund</h5>
                <p class="text-muted small mb-3">Ajukan refund jika ada kendala pada pesanan. Permintaan akan ditinjau oleh
                    tim finance.</p>
                <form method="POST" action="{{ route('customer.orders.refund', $order) }}">
                    @csrf
                    <div class="form-group">
                        <label>Alasan refund</label>
                        <textarea name="refund_reason" class="form-control" rows="3" required>{{ old('refund_reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        Ajukan Refund
                    </button>
                </form>
            @endif

            @if ($order->shipping_address)
                <hr>
                <h5>Informasi Pengiriman</h5>
                <p class="mb-1"><strong>Alamat:</strong> {{ $order->shipping_address }}</p>
                <p class="mb-1"><strong>Kota/Provinsi:</strong> {{ $order->shipping_destination_city_name ?? '-' }},
                    {{ $order->shipping_destination_province_name ?? '-' }}</p>
                <p class="mb-1"><strong>Kurir:</strong> {{ strtoupper($order->shipping_courier ?? '-') }}</p>
                <p class="mb-0"><strong>Resi:</strong> {{ $order->tracking_number ?? '-' }}</p>
            @endif

            @if (in_array($order->status, ['pending', 'confirmed'], true) && $order->gateway_checkout_url)
                <hr>
                <div class="alert alert-info">
                    Pesanan ini masih menunggu penyelesaian pembayaran.
                </div>
                <a href="{{ $order->gateway_checkout_url }}" class="btn btn-success" target="_blank" rel="noopener">
                    Lanjutkan Pembayaran
                </a>
            @endif
        </div>
    </div>
@endsection
