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
            --brand-soft: #e4f4ef;
            --accent: #bb3e03;
            --muted: #6f6759;
            --line: #d9d2c4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background: radial-gradient(circle at 8% 0%, #efe4c9 0, transparent 30%), var(--bg);
            color: var(--ink);
        }

        .container {
            width: min(1140px, 92vw);
            margin: 0 auto;
            padding: 1.4rem 0 2.5rem;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .9rem;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .brand img {
            max-height: 48px;
            width: auto;
            object-fit: contain;
        }

        .back {
            color: var(--brand);
            text-decoration: none;
            font-weight: 700;
        }

        .layout {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.1rem;
            box-shadow: 0 8px 24px rgba(35, 31, 24, 0.06);
        }

        .cover {
            height: 360px;
            border-radius: 12px;
            background: linear-gradient(145deg, #dce8de, #efe7d5);
            display: grid;
            place-items: center;
            text-align: center;
            font-family: 'Fraunces', serif;
            font-size: 1.4rem;
            overflow: hidden;
            border: 1px solid #d8cfbf;
        }

        .cover img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #f6f2e9;
        }

        h1 {
            margin: .85rem 0 .35rem;
            font-family: 'Fraunces', serif;
        }

        .meta-line {
            margin-top: .3rem;
            color: var(--muted);
        }

        .price {
            color: var(--accent);
            font-size: 1.35rem;
            font-weight: 800;
            margin: .55rem 0 .9rem;
        }

        .desc {
            white-space: pre-line;
            line-height: 1.55;
            border-top: 1px dashed var(--line);
            margin-top: .75rem;
            padding-top: .75rem;
        }

        .stock-pill {
            margin-top: .8rem;
            font-size: .88rem;
            display: inline-block;
            border-radius: 999px;
            border: 1px solid #d8cfbf;
            padding: .28rem .6rem;
            background: #fbf8f1;
        }

        .order-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: .6rem;
        }

        .badge {
            border-radius: 999px;
            border: 1px solid #c9ddd1;
            background: #edf7f2;
            color: #1e5d47;
            padding: .2rem .55rem;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .section-title {
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #5f564a;
            margin: .2rem 0 .32rem;
            font-weight: 800;
        }

        .section-box {
            border: 1px solid #e2dacb;
            border-radius: 12px;
            padding: .75rem;
            background: #fcfaf5;
            margin-bottom: .8rem;
        }

        label {
            display: block;
            font-size: .87rem;
            margin-bottom: .28rem;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid #cfc6b6;
            border-radius: 10px;
            padding: .64rem .7rem;
            background: #fff;
            font-family: inherit;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #7eb4a5;
            box-shadow: 0 0 0 3px rgba(126, 180, 165, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }

        .field {
            margin-bottom: .7rem;
        }

        .span-2 {
            grid-column: span 2;
        }

        .hint {
            color: var(--muted);
            font-size: .8rem;
            margin-top: .25rem;
        }

        .btn {
            background: var(--brand);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .82rem 1rem;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
        }

        .btn:hover {
            filter: brightness(1.05);
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

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .span-2 {
                grid-column: auto;
            }

            .cover {
                height: 300px;
            }
        }
    </style>
</head>

<body>
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

    <div class="container">
        <div class="brand">
            <img src="{{ asset('logowide2.png') }}" alt="MS ISBN">
            <a class="back" href="{{ route('store.track.form') }}">Lacak Pesanan</a>
        </div>
        <a class="back" href="{{ route('store.index') }}">&larr; Kembali ke katalog</a>

        <div class="layout">
            <section class="card">
                <div class="cover">
                    @if ($coverUrl)
                        <img src="{{ $coverUrl }}" alt="Sampul {{ $item->title }}">
                    @else
                        <div>{{ $item->title }}</div>
                    @endif
                </div>
                <h1>{{ $item->title }}</h1>
                @if ($item->subtitle)
                    <div class="meta-line">{{ $item->subtitle }}</div>
                @endif
                <div class="meta-line">Penulis: {{ $item->author_name ?: '-' }}</div>
                <div class="meta-line">Tipe: {{ $item->productTypeLabel() }}</div>
                @if ($item->hasSeparateFormats())
                    <div class="price" style="margin-bottom:.3rem;">
                        <span style="font-size:1rem; font-weight:700; color:var(--muted)">Print:</span>
                        Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}
                    </div>
                    <div class="price" style="margin-top:0;">
                        <span style="font-size:1rem; font-weight:700; color:var(--muted)">Ebook:</span>
                        Rp {{ number_format($item->finalEbookPrice(), 0, ',', '.') }}
                    </div>
                @else
                    <div class="price">Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}</div>
                @endif
                <div class="desc">{{ $item->description ?: 'Deskripsi buku belum tersedia.' }}</div>
                @if ($item->isPrint() && $item->stock !== null)
                    <div class="stock-pill">Stok tersedia: <strong>{{ $item->stock }}</strong></div>
                @endif
            </section>

            <section class="card">
                <div class="order-head">
                    <h3 style="margin:0;">Pesan Buku Ini</h3>
                    <span class="badge">Checkout Aman</span>
                </div>

                @if ($item->isPrint() && $item->isEbook())
                    <div class="alert ok" style="background:#e8f7f0;border-color:#b5e2cc;">
                        Produk ini tersedia dalam dua format: <strong>Print</strong> dan <strong>Ebook</strong>.
                        Pilih format yang sesuai kebutuhan Anda di bawah ini.
                    </div>
                @endif

                @if (!empty($shippingMeta['message']) && $item->isPrint())
                    <div class="alert warn">{{ $shippingMeta['message'] }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert warn" style="background:#ffe9e2;border-color:#ffbaa0;">
                        <strong>Periksa data pesanan:</strong>
                        <ul style="margin:.35rem 0 0 1.1rem; padding:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="alert warn">{{ session('warning') }}</div>
                @endif
                @if (session('danger'))
                    <div class="alert warn" style="background:#ffe1e1;border-color:#ffb0b0;">{{ session('danger') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('store.order', $item) }}" id="checkout-form">
                    @csrf

                    @if ($item->hasSeparateFormats())
                        <div class="section-title">Pilih Format</div>
                        <div class="section-box" style="margin-bottom:.8rem;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                                <label id="fmt-print-label"
                                    style="border:2px solid var(--line);border-radius:12px;padding:.75rem;cursor:pointer;text-align:center;transition:border-color .15s;">
                                    <input type="radio" name="selected_format" value="print" id="fmt-print" required
                                        {{ old('selected_format', '') === 'print' ? 'checked' : '' }}
                                        style="margin-right:.4rem;">
                                    <strong>📦 Print</strong>
                                    <div
                                        style="font-size:.88rem;color:var(--accent);font-weight:700;margin-top:.25rem;">
                                        Rp {{ number_format($item->finalPrice(), 0, ',', '.') }}</div>
                                    <div style="font-size:.78rem;color:var(--muted);">Buku cetak dikirim ke alamat</div>
                                </label>
                                <label id="fmt-ebook-label"
                                    style="border:2px solid var(--line);border-radius:12px;padding:.75rem;cursor:pointer;text-align:center;transition:border-color .15s;">
                                    <input type="radio" name="selected_format" value="ebook" id="fmt-ebook"
                                        {{ old('selected_format', '') === 'ebook' ? 'checked' : '' }}
                                        style="margin-right:.4rem;">
                                    <strong>📖 Ebook</strong>
                                    <div
                                        style="font-size:.88rem;color:var(--accent);font-weight:700;margin-top:.25rem;">
                                        Rp {{ number_format($item->finalEbookPrice(), 0, ',', '.') }}</div>
                                    <div style="font-size:.78rem;color:var(--muted);">File digital, akses langsung</div>
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="section-title">Identitas Pembeli</div>
                    <div class="form-grid">
                        <div class="field span-2">
                            <label>Nama</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                maxlength="120" required>
                        </div>
                        <div class="field">
                            <label>No WhatsApp</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                inputmode="numeric" pattern="[0-9+\-\s]{8,32}" maxlength="32" required>
                        </div>
                        <div class="field">
                            <label>Email (opsional)</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}">
                        </div>
                        <div class="field">
                            <label>Qty</label>
                            <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                                required id="order-qty">
                        </div>
                        <div class="field">
                            <label>Total estimasi</label>
                            <input type="text"
                                value="Rp {{ number_format($item->finalPrice(), 0, ',', '.') }} x qty" disabled
                                id="price-preview">
                        </div>
                    </div>

                    @if ($item->isPrint())
                        <div id="section-shipping" class="section-title">Pengiriman Cetak</div>
                        <div id="wrap-shipping" class="section-box">
                            <div class="form-grid">
                                <div class="field">
                                    <label>Provinsi</label>
                                    <select id="shipping-province" name="shipping_destination_province_id">
                                        <option value="">- Pilih Provinsi -</option>
                                        @foreach ($shippingProvinces as $province)
                                            <option value="{{ $province['province_id'] ?? '' }}"
                                                @selected((string) old('shipping_destination_province_id') === (string) ($province['province_id'] ?? ''))>
                                                {{ $province['province'] ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="shipping_destination_province_name"
                                        id="shipping-province-name"
                                        value="{{ old('shipping_destination_province_name') }}">
                                </div>
                                <div class="field">
                                    <label>Kota/Kabupaten</label>
                                    <select id="shipping-city" name="shipping_destination_city_id">
                                        <option value="">- Pilih Kota -</option>
                                    </select>
                                    <input type="hidden" name="shipping_destination_city_name"
                                        id="shipping-city-name" value="{{ old('shipping_destination_city_name') }}">
                                </div>
                                <div class="field">
                                    <label>Kurir</label>
                                    <select name="shipping_courier" id="shipping-courier">
                                        @foreach (['jne' => 'JNE', 'pos' => 'POS', 'tiki' => 'TIKI'] as $key => $label)
                                            <option value="{{ $key }}" @selected(old('shipping_courier', 'jne') === $key)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field span-2" style="margin-bottom:0;">
                                    <label>Alamat Kirim</label>
                                    <textarea name="shipping_address" rows="3" id="shipping-address">{{ old('shipping_address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="field" id="wrap-shipping-simple" style="margin-bottom:.7rem;">
                            <label>Alamat Kirim (opsional untuk ebook)</label>
                            <textarea name="shipping_address" rows="3">{{ old('shipping_address') }}</textarea>
                        </div>
                    @endif

                    @if ($item->isEbook())
                        <div id="section-ebook" class="section-title">Akses Ebook</div>
                        <div id="wrap-ebook" class="section-box" style="margin-bottom:.7rem;">
                            <div class="field" style="margin-bottom:0;">
                                <label>Password Baca Ebook</label>
                                <input type="password" name="reader_password" id="reader-password" minlength="6"
                                    maxlength="64">
                                <div class="hint">Password ini dipakai untuk membuka halaman baca ebook setelah
                                    pembayaran lunas.</div>
                            </div>
                        </div>
                    @endif

                    <div class="section-title">Catatan Tambahan</div>
                    <div class="form-grid">
                        <div class="field span-2">
                            <label>Catatan</label>
                            <textarea name="notes" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                        </div>
                        <div class="field span-2" style="margin-bottom:0;">
                            <button class="btn" type="submit">Checkout via iPaymu</button>
                        </div>
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

    <script>
        (function() {
            const qtyInput = document.getElementById('order-qty');
            const preview = document.getElementById('price-preview');
            const printPrice = {{ (float) $item->finalPrice() }};
            const ebookPrice = {{ (float) $item->finalEbookPrice() }};
            const hasSeparate = {{ $item->hasSeparateFormats() ? 'true' : 'false' }};

            const provinceSelect = document.getElementById('shipping-province');
            const provinceNameInput = document.getElementById('shipping-province-name');
            const citySelect = document.getElementById('shipping-city');
            const cityNameInput = document.getElementById('shipping-city-name');

            const cityEndpoint = '{{ route('store.shipping.cities') }}';
            const oldCityId = '{{ old('shipping_destination_city_id') }}';

            // ── Elemen yang di-toggle berdasarkan format ──────────────────
            const wrapShipping = document.getElementById('wrap-shipping');
            const secShipping = document.getElementById('section-shipping');
            const wrapEbook = document.getElementById('wrap-ebook');
            const secEbook = document.getElementById('section-ebook');
            const readerPassword = document.getElementById('reader-password');
            const shippingProvEl = document.getElementById('shipping-province');
            const shippingCityEl = document.getElementById('shipping-city');
            const shippingCourEl = document.getElementById('shipping-courier');
            const shippingAddrEl = document.getElementById('shipping-address');

            const fmtPrint = document.getElementById('fmt-print');
            const fmtEbook = document.getElementById('fmt-ebook');
            const fmtPrintLabel = document.getElementById('fmt-print-label');
            const fmtEbookLabel = document.getElementById('fmt-ebook-label');

            function formatRupiah(n) {
                return new Intl.NumberFormat('id-ID').format(n);
            }

            function currentBasePrice() {
                if (!hasSeparate) return printPrice;
                if (fmtEbook && fmtEbook.checked) return ebookPrice;
                return printPrice;
            }

            function updatePreview() {
                const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10));
                if (qtyInput && Number.isFinite(qty)) qtyInput.value = qty;
                const base = currentBasePrice();
                if (preview) preview.value = 'Rp ' + formatRupiah(base) + ' x ' + qty + ' = Rp ' + formatRupiah(Math
                    .round(base * qty));
            }

            function setRequired(el, required) {
                if (!el) return;
                if (required) el.setAttribute('required', '');
                else el.removeAttribute('required');
            }

            function toggleFormatSections() {
                if (!hasSeparate) return;

                const isPrint = fmtPrint && fmtPrint.checked;
                const isEbook = fmtEbook && fmtEbook.checked;

                // Highlight selected format card
                if (fmtPrintLabel) fmtPrintLabel.style.borderColor = isPrint ? '#005f73' : '#d9d2c4';
                if (fmtEbookLabel) fmtEbookLabel.style.borderColor = isEbook ? '#005f73' : '#d9d2c4';

                // Show/hide sections
                [wrapShipping, secShipping].forEach(el => {
                    if (el) el.style.display = isPrint ? '' : 'none';
                });
                [wrapEbook, secEbook].forEach(el => {
                    if (el) el.style.display = isEbook ? '' : 'none';
                });

                // Toggle required
                setRequired(shippingProvEl, isPrint);
                setRequired(shippingCityEl, isPrint);
                setRequired(shippingCourEl, isPrint);
                setRequired(shippingAddrEl, isPrint);
                setRequired(readerPassword, isEbook);

                updatePreview();
            }

            function setSelectedLabel(selectEl, hiddenInput) {
                if (!selectEl || !hiddenInput) return;
                const selected = selectEl.options[selectEl.selectedIndex];
                hiddenInput.value = selected && selected.value ? selected.text.trim() : '';
            }

            function resetCityOptions() {
                if (!citySelect) return;
                citySelect.innerHTML = '';
                const ph = document.createElement('option');
                ph.value = '';
                ph.textContent = '- Pilih Kota -';
                citySelect.appendChild(ph);
            }

            async function loadCities(provinceId, selectedCityId = '') {
                if (!citySelect) return;
                resetCityOptions();
                if (!provinceId) return;

                try {
                    const response = await fetch(cityEndpoint + '?province_id=' + encodeURIComponent(provinceId), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!response.ok) return;
                    const payload = await response.json();
                    const cities = payload.data || [];

                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.city_id || '';
                        option.textContent = (city.type ? city.type + ' ' : '') + (city.city_name || '-');
                        if (selectedCityId && String(selectedCityId) === String(option.value)) option
                            .selected = true;
                        citySelect.appendChild(option);
                    });

                    setSelectedLabel(citySelect, cityNameInput);
                } catch (err) {
                    console.error('Gagal memuat kota RajaOngkir:', err);
                }
            }

            // Init
            if (qtyInput) {
                qtyInput.addEventListener('input', updatePreview);
                qtyInput.addEventListener('change', updatePreview);
            }
            updatePreview();

            if (provinceSelect) {
                provinceSelect.addEventListener('change', () => {
                    setSelectedLabel(provinceSelect, provinceNameInput);
                    loadCities(provinceSelect.value, '');
                });
                setSelectedLabel(provinceSelect, provinceNameInput);
                if (provinceSelect.value) loadCities(provinceSelect.value, oldCityId);
            }

            if (citySelect) citySelect.addEventListener('change', () => setSelectedLabel(citySelect, cityNameInput));

            if (fmtPrint) fmtPrint.addEventListener('change', toggleFormatSections);
            if (fmtEbook) fmtEbook.addEventListener('change', toggleFormatSections);

            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', () => {
                    setSelectedLabel(provinceSelect, provinceNameInput);
                    setSelectedLabel(citySelect, cityNameInput);
                });
            }

            // Initial state
            toggleFormatSections();
        })();
    </script>
</body>

</html>
