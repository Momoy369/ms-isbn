<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status {{ $order->order_number }}</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #111827;
        }

        .wrap {
            width: min(860px, 94vw);
            margin: 4vh auto;
            display: grid;
            gap: .9rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1rem;
        }

        .brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .brand img {
            max-height: 48px;
        }

        .muted {
            color: #64748b;
        }

        .badge {
            display: inline-block;
            padding: .25rem .6rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            background: #e2e8f0;
        }

        .btn {
            background: #005f73;
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .65rem .95rem;
            font-weight: 700;
            cursor: pointer;
        }

        input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: .65rem .75rem;
            margin: .4rem 0 .65rem;
        }

        .alert {
            border-radius: 10px;
            padding: .6rem .75rem;
            margin-bottom: .7rem;
        }

        .alert.warn {
            background: #fef3c7;
            border: 1px solid #fcd34d;
        }

        .alert.danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }

        a {
            color: #0f766e;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card brand">
            <img src="{{ asset('logowide2.png') }}" alt="MS ISBN">
            <div>
                <a href="{{ route('store.index') }}">Store</a>
                <span class="muted">|</span>
                <a href="{{ route('store.track.form') }}">Cek order lain</a>
            </div>
        </div>

        <div class="card">
            <div class="muted">Nomor Order</div>
            <h2 style="margin:.15rem 0 .5rem;">{{ $order->order_number }}</h2>
            <span class="badge">{{ strtoupper($order->status) }}</span>
            <div class="muted" style="margin-top:.55rem;">Dibuat: {{ $order->created_at?->format('d M Y H:i') }}</div>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Ringkasan</h3>
            <div>Produk: <strong>{{ $order->item->title ?? '-' }}</strong></div>
            <div>Qty: {{ number_format($order->quantity) }}</div>
            <div>Total: <strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
            <div>Pembeli: {{ $order->customer_name }} ({{ $order->customer_phone }})</div>

            @if (($order->item->product_type ?? 'print') === 'print')
                <hr style="border:none;border-top:1px solid #e5e7eb; margin:.8rem 0;">
                <div>Ongkir: Rp {{ number_format((float) ($order->shipping_cost ?? 0), 0, ',', '.') }}</div>
                <div>Layanan: {{ $order->shipping_service ?: '-' }}
                    {{ $order->shipping_etd ? '(ETD ' . $order->shipping_etd . ' hari)' : '' }}</div>
                <div>Kurir: {{ $order->shipping_courier ?: '-' }}</div>
                <div>No Resi: {{ $order->tracking_number ?: '-' }}</div>
                <div>Alamat: {{ $order->shipping_address ?: '-' }}</div>
            @endif

            @if (($order->item->product_type ?? 'print') === 'ebook')
                <hr style="border:none;border-top:1px solid #e5e7eb; margin:.8rem 0;">
                <h4 style="margin:.2rem 0 .5rem;">Baca Ebook</h4>

                @if (session('warning'))
                    <div class="alert warn">{{ session('warning') }}</div>
                @endif
                @if (session('danger'))
                    <div class="alert danger">{{ session('danger') }}</div>
                @endif

                <form method="POST" action="{{ route('store.reader', $order->order_number) }}">
                    @csrf
                    <label>Password Baca</label>
                    <input type="password" name="password" required>
                    <button class="btn" type="submit">Buka Ebook</button>
                </form>
            @endif
        </div>
    </div>
</body>

</html>
