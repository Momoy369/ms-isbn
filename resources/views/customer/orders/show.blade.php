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
                    <p><strong>Total:</strong> Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Payment Reference:</strong> {{ $order->payment_reference ?? '-' }}</p>
                    <p><strong>Gateway Reference:</strong> {{ $order->gateway_reference ?? '-' }}</p>
                    <p><strong>Dibuat:</strong> {{ optional($order->created_at)->format('d M Y H:i') }}</p>
                    <p><strong>Dibayar:</strong> {{ optional($order->paid_at)->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>

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
                <a href="{{ $order->gateway_checkout_url }}" class="btn btn-success" target="_blank" rel="noopener">
                    Lanjutkan Pembayaran
                </a>
            @endif
        </div>
    </div>
@endsection
