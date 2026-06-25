<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $item->title }} - Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f7f4ea;
            --card: #fff;
            --ink: #231f18;
            --brand: #005f73;
            --accent: #bb3e03;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        .container {
            width: min(1100px, 92vw);
            margin: 0 auto;
            padding: 1.4rem 0 2.5rem;
        }

        .back {
            color: var(--brand);
            text-decoration: none;
            font-weight: 700;
        }

        .layout {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .card {
            background: var(--card);
            border: 1px solid #d9d2c4;
            border-radius: 16px;
            padding: 1rem;
        }

        .cover {
            height: 280px;
            border-radius: 12px;
            background: linear-gradient(145deg, #94d2bd, #e9d8a6);
            display: grid;
            place-items: center;
            text-align: center;
            font-family: 'Fraunces', serif;
            font-size: 1.4rem;
        }

        h1 {
            margin: 0 0 .35rem;
            font-family: 'Fraunces', serif;
        }

        .price {
            color: var(--accent);
            font-size: 1.3rem;
            font-weight: 800;
            margin: .5rem 0 1rem;
        }

        label {
            display: block;
            font-size: .87rem;
            margin-bottom: .28rem;
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid #cfc6b6;
            border-radius: 10px;
            padding: .64rem .7rem;
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem;
        }

        .btn {
            background: var(--brand);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .75rem 1rem;
            font-weight: 700;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            padding: .7rem .85rem;
            margin-bottom: .8rem;
        }

        .ok {
            background: #d9f6ec;
            border: 1px solid #90ddc2;
        }

        .warn {
            background: #fff3dd;
            border: 1px solid #ffd48a;
        }

        .related {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: .7rem;
        }

        .small {
            background: #fff;
            border: 1px solid #ded5c7;
            border-radius: 12px;
            padding: .7rem;
        }

        @media (max-width: 860px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .grid2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a class="back" href="{{ route('store.index') }}">&larr; Kembali ke katalog</a>

        <div class="layout">
            <section class="card">
                <div class="cover">{{ $item->title }}</div>
                <h1>{{ $item->title }}</h1>
                @if ($item->subtitle)
                    <div>{{ $item->subtitle }}</div>
                @endif
                <div style="margin-top:.35rem; color:#6f6759;">Penulis: {{ $item->author_name ?: '-' }}</div>
                <div class="price">Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}</div>
                <div style="white-space: pre-line;">{{ $item->description ?: 'Deskripsi buku belum tersedia.' }}</div>
                @if ($item->stock !== null)
                    <div style="margin-top:.8rem; font-size:.9rem;">Stok: <strong>{{ $item->stock }}</strong></div>
                @endif
            </section>

            <section class="card">
                <h3 style="margin-top:0;">Pesan Buku Ini</h3>
                @if (session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="alert warn">{{ session('warning') }}</div>
                @endif

                <form method="POST" action="{{ route('store.order', $item) }}">
                    @csrf
                    <div style="margin-bottom:.65rem;">
                        <label>Nama</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="grid2">
                        <div>
                            <label>No WhatsApp</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required>
                        </div>
                        <div>
                            <label>Email (opsional)</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}">
                        </div>
                    </div>
                    <div class="grid2" style="margin-top:.65rem;">
                        <div>
                            <label>Qty</label>
                            <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                                required>
                        </div>
                        <div>
                            <label>Total estimasi</label>
                            <input type="text"
                                value="Rp {{ number_format($item->finalPrice(), 0, ',', '.') }} x qty" disabled>
                        </div>
                    </div>
                    <div style="margin-top:.65rem;">
                        <label>Alamat Kirim</label>
                        <textarea name="shipping_address" rows="3">{{ old('shipping_address') }}</textarea>
                    </div>
                    <div style="margin-top:.65rem;">
                        <label>Catatan</label>
                        <textarea name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <div style="margin-top:.75rem;">
                        <button class="btn" type="submit">Kirim Pesanan</button>
                    </div>
                </form>
            </section>
        </div>

        @if ($related->isNotEmpty())
            <h3 style="margin-top:1.3rem;">Buku Lainnya</h3>
            <section class="related">
                @foreach ($related as $row)
                    <article class="small">
                        <div style="font-weight:800;">{{ $row->title }}</div>
                        <div style="font-size:.85rem; color:#6c6457;">{{ $row->author_name ?: '-' }}</div>
                        <div style="margin:.4rem 0; color:#bb3e03; font-weight:700;">Rp
                            {{ number_format($row->finalPrice(), 0, ',', '.') }}</div>
                        <a class="back" href="{{ route('store.show', $row->slug) }}">Lihat</a>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</body>

</html>
