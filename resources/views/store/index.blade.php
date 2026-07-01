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

        .hero-menu {
            display: flex;
            gap: .55rem;
            flex-wrap: wrap;
            margin-top: .9rem;
        }

        .hero-menu a {
            text-decoration: none;
            color: #264653;
            background: #fff5de;
            border: 1px solid #e5d4af;
            border-radius: 999px;
            padding: .28rem .7rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .hero-copy {
            margin-top: .9rem;
            border-left: 4px solid #ca6702;
            padding: .55rem .75rem;
            background: #fff8ea;
            border-radius: 8px;
            max-width: 760px;
            font-size: .94rem;
            line-height: 1.45;
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

        .toolbar select {
            border: 1px solid #c4beb3;
            border-radius: 12px;
            padding: .75rem .9rem;
            background: #fff;
            min-width: 170px;
        }

        .toolbar .check {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid #c4beb3;
            border-radius: 12px;
            padding: .65rem .8rem;
            background: #fff;
            font-size: .86rem;
            color: #4a4036;
        }

        .toolbar .check input {
            min-width: auto;
            flex: 0;
            width: auto;
            margin: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .8rem;
            margin: 0 0 1.4rem;
        }

        .stat-card {
            background: #fffaf0;
            border: 1px solid #d8cfbf;
            border-radius: 14px;
            padding: .8rem .9rem;
        }

        .stat-card small {
            color: #6f6355;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .stat-card strong {
            display: block;
            font-size: 1.25rem;
            margin-top: .2rem;
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
            overflow: hidden;
            border-bottom: 1px solid #ded7cc;
        }

        .cover img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #f6f2e9;
        }

        .body {
            padding: .9rem;
            display: grid;
            gap: .5rem;
            background: #fffdf8;
        }

        .meta {
            font-size: .84rem;
            color: #5f564a;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d8cfbf;
            background: #f6efe2;
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .72rem;
            font-weight: 700;
            color: #7a4f1b;
            width: fit-content;
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

        .account-menu {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .account-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .28rem .65rem;
            border-radius: 999px;
            background: #fff5de;
            border: 1px solid #e5d4af;
            color: #5d4a22;
            font-size: .78rem;
            font-weight: 800;
        }

        .filter-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin: -.9rem 0 1.2rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            border: 1px solid #d8cfbf;
            border-radius: 999px;
            padding: .25rem .55rem;
            font-size: .76rem;
            font-weight: 700;
            color: #5d5042;
            background: #fff8ea;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .7rem;
            margin: 0 0 .8rem;
            flex-wrap: wrap;
        }

        .section-head h2 {
            margin: 0;
            font-family: 'Fraunces', serif;
            font-size: clamp(1.2rem, 3vw, 1.7rem);
        }

        .section-head p {
            margin: 0;
            color: #5f564a;
            font-size: .9rem;
        }

        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: .9rem;
            margin: 1.2rem 0 2rem;
        }

        .package-card {
            background: #fffef8;
            border: 1px solid #ddd4c4;
            border-radius: 14px;
            padding: .95rem;
            box-shadow: 0 4px 14px rgba(31, 26, 22, 0.05);
        }

        .package-name {
            font-weight: 800;
            margin: 0 0 .35rem;
        }

        .package-price {
            color: #bb3e03;
            font-weight: 800;
            margin: .4rem 0;
        }

        .package-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .5rem;
        }

        .tag {
            border-radius: 999px;
            border: 1px solid #d7cdbe;
            background: #f8f2e6;
            padding: .18rem .5rem;
            font-size: .72rem;
            font-weight: 700;
            color: #5d5042;
        }

        .flow-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .75rem;
            margin: 1rem 0 1.8rem;
        }

        .flow-card {
            border: 1px solid #ddd4c4;
            border-radius: 12px;
            background: #fff;
            padding: .75rem;
        }

        .flow-card strong {
            color: #005f73;
        }

        .faq {
            margin: 1rem 0 2.5rem;
            display: grid;
            gap: .55rem;
        }

        .faq details {
            background: #fff;
            border: 1px solid #ddd4c4;
            border-radius: 10px;
            padding: .65rem .8rem;
        }

        .faq summary {
            font-weight: 700;
            cursor: pointer;
        }

        .faq p {
            margin: .45rem 0 0;
            color: #5f564a;
        }
    </style>
</head>

<body>
    @php
        $isGuest = !auth()->check();
        $currentRole = $isGuest ? null : (string) auth()->user()->role;
        $storeReturnUrl = request()->fullUrl();

        if ($isGuest) {
            $consultationUrl = route('register', [
                'from' => 'store',
                'return_to' => $storeReturnUrl,
                'intent' => 'publishing-package',
            ]);
            $consultationLabel = 'Mulai Konsultasi Paket';
        } elseif (in_array($currentRole, ['customer', 'reader'], true)) {
            $consultationUrl = route('profile.edit', [
                'source' => 'storefront',
                'intent' => 'publishing-package',
            ]);
            $consultationLabel = 'Ajukan Konsultasi Paket';
        } elseif (in_array($currentRole, ['author'], true)) {
            $consultationUrl = route('author.dashboard');
            $consultationLabel = 'Lanjutkan Sebagai Author';
        } else {
            $consultationUrl = route('dashboard');
            $consultationLabel = 'Kelola Dari Panel Internal';
        }
    @endphp
    <div class="container">
        <section class="hero">
            <div class="brand">
                <img src="{{ asset('logowide.png') }}" alt="MS ISBN">
                <div class="account-menu">
                    <a href="{{ route('store.track.form') }}" class="top-link">Lacak Pesanan</a>
                    @if ($isGuest)
                        <a href="{{ route('login', ['from' => 'store', 'return_to' => $storeReturnUrl]) }}"
                            class="top-link">Masuk Customer</a>
                        <a href="{{ route('register', ['from' => 'store', 'return_to' => $storeReturnUrl]) }}"
                            class="top-link">Daftar Customer</a>
                    @elseif (in_array($currentRole, ['author'], true))
                        <span class="account-badge">Akun Author</span>
                        <a href="{{ route('customer.dashboard') }}" class="top-link">Dashboard Customer</a>
                        <a href="{{ route('author.dashboard') }}" class="top-link">Dashboard Author</a>
                        <a href="{{ route('store.index') }}" class="top-link">Storefront</a>
                    @elseif (in_array($currentRole, ['customer', 'reader'], true))
                        <span class="account-badge">Akun Customer</span>
                        <a href="{{ route('customer.dashboard') }}" class="top-link">Dashboard Customer</a>
                        <a href="{{ route('store.index') }}" class="top-link">Storefront</a>
                    @else
                        <span class="account-badge">Akun Staff</span>
                        <a href="{{ route('dashboard') }}" class="top-link">Panel Internal</a>
                    @endif
                </div>
            </div>
            <h1>Toko Buku Pilihan</h1>
            <p>Katalog buku cetak dan ebook yang dipublikasikan admin. Checkout langsung memakai iPaymu dan lacak
                pesanan secara mandiri.</p>
            <div class="hero-menu">
                <a href="#katalog-buku">Katalog Buku</a>
                <a href="#paket-penerbitan">Paket Penerbitan</a>
                <a href="#cara-kerja">Cara Kerja</a>
                <a href="{{ route('store.policies') }}">Kebijakan</a>
                <a href="#faq-store">FAQ</a>
            </div>
            <div class="hero-copy">
                Dari naskah jadi karya terbit: pilih buku siap beli atau eksplor paket penerbitan untuk membantu proses
                editing, layout, cover, ISBN, hingga publikasi print/ebook dalam satu alur terstruktur.
            </div>
            <form method="GET" class="toolbar">
                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Cari judul atau nama penulis">
                <select name="type">
                    <option value="all" {{ $productType === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                    <option value="print" {{ $productType === 'print' ? 'selected' : '' }}>Print</option>
                    <option value="ebook" {{ $productType === 'ebook' ? 'selected' : '' }}>Ebook</option>
                    <option value="print_ebook" {{ $productType === 'print_ebook' ? 'selected' : '' }}>Print + Ebook
                    </option>
                </select>
                <select name="sort">
                    <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Unggulan</option>
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                    <option value="title_asc" {{ $sort === 'title_asc' ? 'selected' : '' }}>Judul A-Z</option>
                </select>
                <input type="number" name="min_price" value="{{ $minPriceInput }}" min="0"
                    placeholder="Harga min">
                <input type="number" name="max_price" value="{{ $maxPriceInput }}" min="0"
                    placeholder="Harga max">
                <label class="check">
                    <input type="checkbox" name="featured" value="1" {{ $onlyFeatured ? 'checked' : '' }}>
                    Hanya Unggulan
                </label>
                <label class="check">
                    <input type="checkbox" name="promo" value="1" {{ $onlyPromo ? 'checked' : '' }}>
                    Hanya Promo
                </label>
                <button type="submit" class="btn">Cari</button>
                @if ($hasActiveFilters)
                    <a href="{{ route('store.index') }}" class="btn" style="background:#8d806f;">Reset Filter</a>
                @endif
            </form>

            @if ($hasActiveFilters)
                <div class="filter-chips">
                    @if ($search !== '')
                        <span class="chip">Cari: {{ $search }}</span>
                    @endif
                    @if ($productType !== 'all')
                        <span class="chip">Tipe: {{ strtoupper(str_replace('_', ' + ', $productType)) }}</span>
                    @endif
                    @if ($sort !== 'featured')
                        <span class="chip">Sort: {{ str_replace('_', ' ', strtoupper($sort)) }}</span>
                    @endif
                    @if ($minPriceInput !== '')
                        <span class="chip">Min: Rp {{ number_format((float) $minPriceInput, 0, ',', '.') }}</span>
                    @endif
                    @if ($maxPriceInput !== '')
                        <span class="chip">Max: Rp {{ number_format((float) $maxPriceInput, 0, ',', '.') }}</span>
                    @endif
                    @if ($onlyFeatured)
                        <span class="chip">Unggulan</span>
                    @endif
                    @if ($onlyPromo)
                        <span class="chip">Promo</span>
                    @endif
                </div>
            @endif

            <section class="stats">
                <div class="stat-card">
                    <small>Total Katalog Aktif</small>
                    <strong>{{ number_format($stats['total']) }}</strong>
                </div>
                <div class="stat-card">
                    <small>Produk Unggulan</small>
                    <strong>{{ number_format($stats['featured']) }}</strong>
                </div>
                <div class="stat-card">
                    <small>Format Ebook Tersedia</small>
                    <strong>{{ number_format($stats['ebook_ready']) }}</strong>
                </div>
            </section>
        </section>

        @if (session('success'))
            <div class="empty" style="border-style:solid; border-color:#94d2bd; margin-bottom:1rem;">
                {{ session('success') }}</div>
        @endif

        <section id="katalog-buku">
            <div class="section-head">
                <div>
                    <h2>Katalog Buku</h2>
                    <p>Pilih format print, ebook, atau kombinasi sesuai kebutuhan pembaca.</p>
                </div>
            </div>
        </section>

        @if ($items->isEmpty())
            <div class="empty">Belum ada buku yang dipublikasikan di store.</div>
        @else
            <section class="grid">
                @foreach ($items as $item)
                    <article class="card">
                        @php
                            $coverPath = (string) ($item->cover_image_path ?? '');
                            $coverUrl = null;

                            if ($coverPath !== '') {
                                $coverUrl =
                                    str_starts_with($coverPath, 'http://') ||
                                    str_starts_with($coverPath, 'https://') ||
                                    str_starts_with($coverPath, '/')
                                        ? $coverPath
                                        : asset('storage/' . ltrim($coverPath, '/'));
                            }
                        @endphp
                        <div class="cover">
                            @if ($coverUrl)
                                <img src="{{ $coverUrl }}" alt="Sampul {{ $item->title }}">
                            @else
                                {{ $item->title }}
                            @endif
                        </div>
                        <div class="body">
                            <div style="font-weight:800;">{{ $item->title }}</div>
                            <div class="meta">{{ $item->author_name ?: 'Penulis belum diisi' }}</div>
                            <div class="meta">{{ $item->productTypeLabel() }}</div>
                            @if ($item->hasSeparateFormats())
                                <div class="meta-pill">Pilih format saat checkout</div>
                            @endif
                            <div class="price">Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}</div>
                            <a href="{{ route('store.show', $item->slug) }}" class="btn"
                                style="text-align:center;">Lihat Detail</a>
                        </div>
                    </article>
                @endforeach
            </section>
            <div class="pagination">{{ $items->links() }}</div>
        @endif

        <section id="paket-penerbitan">
            <div class="section-head">
                <div>
                    <h2>Paket Penerbitan</h2>
                    <p>Untuk calon penulis yang ingin menerbitkan karya, bukan hanya membeli buku jadi.</p>
                </div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <a href="{{ $consultationUrl }}" class="btn">{{ $consultationLabel }}</a>
                    <a href="{{ route('store.policies') }}#faq-author" class="btn"
                        style="background:#8d806f;">Lihat FAQ Author</a>
                </div>
            </div>

            @if ($packages->isEmpty())
                <div class="empty">Belum ada paket penerbitan yang ditampilkan.</div>
            @else
                <div class="package-grid">
                    @foreach ($packages as $package)
                        <article class="package-card">
                            <div class="package-name">{{ $package->name }}</div>
                            @if ($package->description)
                                <div class="meta">{{ $package->description }}</div>
                            @endif
                            <div class="package-price">Rp {{ number_format((float) $package->price, 0, ',', '.') }}
                            </div>
                            <div class="package-tags">
                                <span
                                    class="tag">{{ $package->supports_print ? 'Support Print' : 'Tanpa Print' }}</span>
                                <span
                                    class="tag">{{ $package->supports_ebook ? 'Support Ebook' : 'Tanpa Ebook' }}</span>
                                @if ($package->includes_editing)
                                    <span class="tag">Editing</span>
                                @endif
                                @if ($package->includes_layout)
                                    <span class="tag">Layout</span>
                                @endif
                                @if ($package->includes_cover_design)
                                    <span class="tag">Cover Design</span>
                                @endif
                            </div>
                            @php
                                $packageQuery = ['package' => $package->name, 'intent' => 'publishing-package'];

                                if ($isGuest) {
                                    $packageConsultationUrl = route('register', [
                                        'from' => 'store',
                                        'return_to' => $storeReturnUrl,
                                        ...$packageQuery,
                                    ]);
                                } elseif (in_array($currentRole, ['customer', 'reader'], true)) {
                                    $packageConsultationUrl = route('profile.edit', [
                                        'source' => 'storefront',
                                        ...$packageQuery,
                                    ]);
                                } elseif (in_array($currentRole, ['author'], true)) {
                                    $packageConsultationUrl = route('author.dashboard');
                                } else {
                                    $packageConsultationUrl = route('dashboard');
                                }
                            @endphp
                            <div style="margin-top:.75rem;">
                                <a href="{{ $packageConsultationUrl }}" class="btn"
                                    style="width:100%; text-align:center;">Pilih Paket Ini</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section id="kebijakan-store" style="margin:1rem 0 2rem;">
            <div class="section-head">
                <div>
                    <h2>Kebijakan Storefront</h2>
                    <p>Ringkasan aturan layanan agar proses pembelian dan pemesanan paket lebih transparan.</p>
                </div>
                <a href="{{ route('store.policies') }}" class="btn" style="background:#8d806f;">Baca Kebijakan
                    Lengkap</a>
            </div>
            <div class="flow-grid">
                <div class="flow-card">
                    <strong>Refund & Komplain</strong>
                    <div class="meta">Permintaan refund diajukan dari detail order customer dan ditinjau finance
                        sesuai
                        bukti transaksi serta status pesanan.</div>
                </div>
                <div class="flow-card">
                    <strong>Pengiriman Buku Cetak</strong>
                    <div class="meta">Estimasi ongkir dihitung saat checkout. Nomor resi tersedia setelah order
                        diproses dan dikirim.</div>
                </div>
                <div class="flow-card">
                    <strong>Akses Ebook</strong>
                    <div class="meta">Ebook dibuka melalui library internal berbasis password reader pada order yang
                        valid.</div>
                </div>
                <div class="flow-card">
                    <strong>Hak Cipta Karya Author</strong>
                    <div class="meta">Karya tetap milik author sesuai kesepakatan layanan. Ruang lingkup layanan
                        mengikuti paket yang dipilih.</div>
                </div>
            </div>
        </section>

        <section id="cara-kerja">
            <div class="section-head">
                <div>
                    <h2>Cara Kerja</h2>
                    <p>Alur singkat layanan storefront dan paket penerbitan.</p>
                </div>
            </div>
            <div class="flow-grid">
                <div class="flow-card">
                    <strong>1. Pilih Kebutuhan</strong>
                    <div class="meta">Beli buku siap baca atau pilih paket penerbitan untuk naskah Anda.</div>
                </div>
                <div class="flow-card">
                    <strong>2. Checkout / Konsultasi</strong>
                    <div class="meta">Proses pembayaran cepat via iPaymu atau kirim minat paket ke tim penerbit.
                    </div>
                </div>
                <div class="flow-card">
                    <strong>3. Produksi & Publikasi</strong>
                    <div class="meta">Untuk paket, tim memproses editing, layout, dan publikasi sesuai lingkup paket.
                    </div>
                </div>
                <div class="flow-card">
                    <strong>4. Monitoring Status</strong>
                    <div class="meta">Pantau status order dan invoice dari dashboard customer atau menu pelacakan.
                    </div>
                </div>
            </div>
        </section>

        <section id="faq-store" class="faq">
            <div class="section-head">
                <div>
                    <h2>FAQ</h2>
                    <p>Pertanyaan paling sering ditanyakan customer dan author.</p>
                </div>
            </div>

            <details open>
                <summary>FAQ Customer: Apakah ebook memiliki batas stok?</summary>
                <p>Tidak. Produk ebook dijual tanpa batas stok dan bisa dibeli kapan saja.</p>
            </details>
            <details>
                <summary>FAQ Customer: Bagaimana cara melacak order?</summary>
                <p>Gunakan menu Lacak Pesanan di atas, atau cek detail order dari dashboard customer.</p>
            </details>
            <details>
                <summary>FAQ Customer: Apakah bisa ajukan refund?</summary>
                <p>Bisa. Refund diajukan dari halaman detail order dan diproses oleh tim finance sesuai status order.
                </p>
            </details>
            <details>
                <summary>FAQ Author: Bisa langsung konsultasi paket penerbitan?</summary>
                <p>Bisa. Klik tombol konsultasi paket lalu isi kebutuhan naskah agar tim penerbit dapat menindaklanjuti.
                </p>
            </details>
            <details>
                <summary>FAQ Author: Apakah saya harus punya akun author dulu?</summary>
                <p>Tidak wajib. Anda bisa mulai sebagai customer, lalu upgrade ke author saat proses layanan
                    penerbitan berjalan.</p>
            </details>
            <details>
                <summary>FAQ Author: Apa saja yang termasuk dalam paket?</summary>
                <p>Tiap paket bisa mencakup editing, layout, cover design, dukungan print, dan/atau ebook sesuai tag
                    pada kartu paket.</p>
            </details>
        </section>
    </div>
</body>

</html>
