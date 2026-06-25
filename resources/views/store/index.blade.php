<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Buku - MS ISBN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f3efe4;
            --ink: #1f1a16;
            --brand: #005f73;
            --brand-soft: #94d2bd;
            --accent: #ca6702;
            --card: #fffdf8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 10%, #e9d8a6 0, transparent 35%),
                radial-gradient(circle at 90% 0%, #94d2bd 0, transparent 30%),
                var(--bg);
            min-height: 100vh;
        }

        .container {
            width: min(1100px, 92vw);
            margin: 0 auto;
        }

        .hero {
            padding: 2.5rem 0 1.5rem;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .brand img {
            max-height: 58px;
            width: auto;
            object-fit: contain;
        }

        .hero h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            margin: 0 0 .5rem;
        }

        .hero p {
            margin: 0;
            max-width: 620px;
        }

        .toolbar {
            display: flex;
            gap: .75rem;
            margin: 1.5rem 0 2rem;
            flex-wrap: wrap;
        }

        .toolbar input {
            flex: 1;
            min-width: 220px;
            border: 1px solid #c4beb3;
            border-radius: 12px;
            padding: .75rem .9rem;
            background: #fff;
        }

        .btn {
            background: var(--brand);
            color: #fff;
            border: 0;
            padding: .75rem 1.1rem;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1rem;
            padding-bottom: 2rem;
        }

        .card {
            background: var(--card);
            border: 1px solid #ded7cc;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 6px 20px rgba(31, 26, 22, 0.06);
            transform: translateY(16px);
            opacity: 0;
            animation: rise .45s ease forwards;
        }

        .card:nth-child(2n) {
            animation-delay: .06s;
        }

        .card:nth-child(3n) {
            animation-delay: .12s;
        }

        @keyframes rise {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .cover {
            height: 170px;
            background: linear-gradient(135deg, var(--brand-soft), #e9d8a6);
            display: grid;
            place-items: center;
            font-family: 'Fraunces', serif;
            font-size: 1.1rem;
            text-align: center;
            padding: 1rem;
        }

        .body {
            padding: .9rem;
            display: grid;
            gap: .5rem;
        }

        .meta {
            font-size: .84rem;
            color: #5f564a;
        }

        .price {
            font-weight: 800;
            color: var(--accent);
        }

        .empty {
            background: #fff;
            border: 1px dashed #b9b0a2;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }

        .pagination {
            margin: 1rem 0 2.5rem;
        }

        .top-link {
            color: var(--brand);
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container">
        <section class="hero">
            <div class="brand">
                <img src="{{ asset('logowide.png') }}" alt="MS ISBN">
                <div>
                    <a href="{{ route('store.track.form') }}" class="top-link" style="margin-right:.75rem;">Lacak
                        Pesanan</a>
                    <a href="{{ route('login') }}" class="top-link">Masuk Admin</a>
                </div>
            </div>
            <h1>Toko Buku Pilihan</h1>
            <p>Katalog buku cetak dan ebook yang dipublikasikan admin. Checkout langsung memakai iPaymu dan lacak
                pesanan secara mandiri.</p>
            <form method="GET" class="toolbar">
                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Cari judul atau nama penulis">
                <button type="submit" class="btn">Cari</button>
            </form>
        </section>

        @if (session('success'))
            <div class="empty" style="border-style:solid; border-color:#94d2bd; margin-bottom:1rem;">
                {{ session('success') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="empty">Belum ada buku yang dipublikasikan di store.</div>
        @else
            <section class="grid">
                @foreach ($items as $item)
                    <article class="card">
                        <div class="cover">{{ $item->title }}</div>
                        <div class="body">
                            <div style="font-weight:800;">{{ $item->title }}</div>
                            <div class="meta">{{ $item->author_name ?: 'Penulis belum diisi' }}</div>
                            <div class="meta">{{ strtoupper($item->product_type ?? 'print') }}</div>
                            <div class="price">Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}</div>
                            <a href="{{ route('store.show', $item->slug) }}" class="btn"
                                style="text-align:center;">Lihat Detail</a>
                        </div>
                    </article>
                @endforeach
            </section>
            <div class="pagination">{{ $items->links() }}</div>
        @endif
    </div>
</body>

</html>
