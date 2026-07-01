<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurator Paket Penerbitan - MS ISBN</title>
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
            --line: #d8cfbf;
            --muted: #63594d;
            --accent: #ca6702;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 7% 0%, #e9d8a6 0, transparent 30%), var(--bg);
        }

        .container {
            width: min(1100px, 92vw);
            margin: 0 auto;
            padding: 1.8rem 0 2.5rem;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .7rem;
            flex-wrap: wrap;
            margin-bottom: .9rem;
        }

        .top img {
            max-height: 54px;
            width: auto;
            object-fit: contain;
        }

        .top a {
            text-decoration: none;
            color: var(--brand);
            font-weight: 800;
        }

        h1 {
            font-family: 'Fraunces', serif;
            margin: .3rem 0 .45rem;
            font-size: clamp(1.85rem, 3.8vw, 2.7rem);
        }

        .lead {
            margin: 0 0 1.1rem;
            color: var(--muted);
            max-width: 760px;
            line-height: 1.52;
        }

        .layout {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 1rem;
        }

        .card {
            background: #fffdf8;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1rem;
            box-shadow: 0 6px 18px rgba(31, 26, 22, 0.06);
        }

        .section-title {
            margin: 0 0 .4rem;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            font-weight: 800;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }

        .field {
            margin-bottom: .75rem;
        }

        .span-2 {
            grid-column: span 2;
        }

        label {
            display: block;
            margin-bottom: .3rem;
            font-size: .88rem;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #cdc4b4;
            border-radius: 10px;
            padding: .63rem .7rem;
            font-family: inherit;
            background: #fff;
        }

        .service-grid {
            display: grid;
            gap: .48rem;
        }

        .service-item {
            border: 1px solid #e2dacb;
            border-radius: 10px;
            background: #faf4e8;
            padding: .55rem .65rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
        }

        .service-item label {
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-weight: 600;
        }

        .service-price {
            font-size: .8rem;
            color: #5b4f42;
            font-weight: 800;
        }

        .estimate {
            display: grid;
            gap: .48rem;
            margin-bottom: .8rem;
        }

        .estimate-row {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            font-size: .9rem;
            color: #52473b;
        }

        .estimate-row.total {
            border-top: 1px dashed var(--line);
            padding-top: .55rem;
            margin-top: .2rem;
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--accent);
        }

        .alert {
            border-radius: 10px;
            padding: .65rem .8rem;
            margin-bottom: .75rem;
        }

        .ok {
            background: #d9f6ec;
            border: 1px solid #90ddc2;
        }

        .warn {
            background: #ffece4;
            border: 1px solid #f2c5b1;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            background: var(--brand);
            color: #fff;
            font-weight: 800;
            width: 100%;
            padding: .82rem 1rem;
            cursor: pointer;
        }

        .hint {
            margin-top: .35rem;
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.45;
        }

        @media (max-width: 960px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .span-2 {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top">
            <img src="{{ asset('logowide.png') }}" alt="MS ISBN">
            <a href="{{ route('store.index') }}">&larr; Kembali ke Storefront</a>
        </div>

        <h1>Configurator Paket Penerbitan</h1>
        <p class="lead">Pilih paket dasar, tambah layanan opsional, lalu kirim request konsultasi. Estimasi dihitung
            otomatis sebagai acuan awal sebelum tim kami menyusun penawaran final.</p>

        <div class="layout">
            <section class="card">
                @if (session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert warn">
                        <strong>Mohon periksa input berikut:</strong>
                        <ul style="margin:.35rem 0 0 1.1rem; padding:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('store.package-configurator.submit') }}" id="package-config-form">
                    @csrf

                    <div class="section-title">Paket Dasar</div>
                    <div class="field">
                        <label for="publishing_package_id">Pilih Paket</label>
                        <select name="publishing_package_id" id="publishing_package_id" required>
                            <option value="">- Pilih paket -</option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}" data-base-price="{{ (float) $package->price }}"
                                    @selected((string) old('publishing_package_id', optional($selectedPackage)->id) === (string) $package->id)>
                                    {{ $package->name }} (Rp {{ number_format((float) $package->price, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="section-title">Layanan Opsional</div>
                    <div class="service-grid field">
                        @php
                            $oldServices = collect(old('services', []))->map(fn($value) => (int) $value)->all();
                        @endphp
                        @forelse ($services as $service)
                            <div class="service-item">
                                <label>
                                    <input type="checkbox" name="services[]" value="{{ (int) $service->id }}"
                                        data-service-price="{{ (float) $service->price }}"
                                        @checked(in_array((int) $service->id, $oldServices, true))>
                                    <span>{{ $service->name }}</span>
                                </label>
                                <span class="service-price">+ Rp
                                    {{ number_format((float) $service->price, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="service-item" style="justify-content:flex-start;">
                                <span class="hint" style="margin:0;">Belum ada layanan opsional aktif. Silakan
                                    aktifkan dari
                                    menu Master Layanan Tambahan.</span>
                            </div>
                        @endforelse
                    </div>

                    <div class="section-title">Data Naskah & Kontak</div>
                    <div class="grid">
                        <div class="field">
                            <label>Nama</label>
                            <input type="text" name="customer_name" maxlength="120"
                                value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="field">
                            <label>No WhatsApp</label>
                            <input type="text" name="customer_phone" maxlength="32"
                                value="{{ old('customer_phone') }}" required>
                        </div>
                        <div class="field span-2">
                            <label>Email (opsional)</label>
                            <input type="email" name="customer_email" maxlength="120"
                                value="{{ old('customer_email') }}">
                        </div>
                        <div class="field span-2">
                            <label>Judul Naskah (opsional)</label>
                            <input type="text" name="manuscript_title" maxlength="190"
                                value="{{ old('manuscript_title') }}">
                        </div>
                        <div class="field">
                            <label>Genre Naskah</label>
                            <input type="text" name="manuscript_genre" maxlength="120"
                                value="{{ old('manuscript_genre') }}" placeholder="Contoh: Novel, Bisnis, Pendidikan">
                        </div>
                        <div class="field">
                            <label>Estimasi Jumlah Halaman</label>
                            <input type="number" name="estimated_page_count" min="10" max="5000"
                                value="{{ old('estimated_page_count') }}" id="estimated_page_count">
                        </div>
                        <div class="field">
                            <label>Target Terbit</label>
                            <input type="date" name="target_publish_date" value="{{ old('target_publish_date') }}">
                        </div>
                        <div class="field">
                            <label>Rentang Budget</label>
                            <select name="budget_range">
                                <option value="">- Pilih budget -</option>
                                @foreach ($budgetOptions as $budgetOption)
                                    <option value="{{ $budgetOption }}" @selected(old('budget_range') === $budgetOption)>
                                        {{ $budgetOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field span-2">
                            <label>Catatan Tambahan</label>
                            <textarea name="notes" rows="3" maxlength="3000">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn">Kirim Permintaan Konsultasi</button>
                    <div class="hint">Setelah form terkirim, tim finance/admin akan menerima notifikasi lead baru
                        untuk
                        ditindaklanjuti.</div>
                </form>
            </section>

            <aside class="card">
                <div class="section-title">Estimasi Biaya Sementara</div>
                <div class="estimate">
                    <div class="estimate-row">
                        <span>Paket dasar</span>
                        <strong id="estimate-base">Rp 0</strong>
                    </div>
                    <div class="estimate-row">
                        <span>Layanan tambahan</span>
                        <strong id="estimate-services">Rp 0</strong>
                    </div>
                    <div class="estimate-row">
                        <span>Surcharge halaman > 220</span>
                        <strong id="estimate-pages">Rp 0</strong>
                    </div>
                    <div class="estimate-row total">
                        <span>Total estimasi</span>
                        <strong id="estimate-total">Rp 0</strong>
                    </div>
                </div>
                <div class="hint">
                    Rumus MVP:
                    <br>1) Paket dasar sesuai pilihan.
                    <br>2) Layanan opsional dijumlahkan.
                    <br>3) Jika halaman naskah > 220, ada surcharge Rp 1.500 per halaman ekstra.
                </div>
                <div class="hint" style="margin-top:.8rem;">
                    Estimasi ini bukan invoice final. Nilai akhir dapat disesuaikan setelah review naskah, kompleksitas
                    layout, dan kebutuhan produksi aktual.
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function() {
            const packageSelect = document.getElementById('publishing_package_id');
            const pageCountInput = document.getElementById('estimated_page_count');
            const serviceInputs = Array.from(document.querySelectorAll('input[name="services[]"]'));

            const baseEl = document.getElementById('estimate-base');
            const serviceEl = document.getElementById('estimate-services');
            const pagesEl = document.getElementById('estimate-pages');
            const totalEl = document.getElementById('estimate-total');

            function formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
            }

            function getBasePrice() {
                const opt = packageSelect?.options[packageSelect.selectedIndex];
                if (!opt) return 0;
                return Number(opt.getAttribute('data-base-price') || 0);
            }

            function getServiceTotal() {
                return serviceInputs
                    .filter((input) => input.checked)
                    .reduce((sum, input) => sum + Number(input.getAttribute('data-service-price') || 0), 0);
            }

            function getPageSurcharge() {
                const pages = Number(pageCountInput?.value || 0);
                if (!Number.isFinite(pages) || pages <= 220) return 0;
                return (pages - 220) * 1500;
            }

            function updateEstimate() {
                const base = getBasePrice();
                const serviceTotal = getServiceTotal();
                const pageSurcharge = getPageSurcharge();
                const total = Math.max(0, base + serviceTotal + pageSurcharge);

                baseEl.textContent = formatRupiah(base);
                serviceEl.textContent = formatRupiah(serviceTotal);
                pagesEl.textContent = formatRupiah(pageSurcharge);
                totalEl.textContent = formatRupiah(total);
            }

            packageSelect?.addEventListener('change', updateEstimate);
            pageCountInput?.addEventListener('input', updateEstimate);
            serviceInputs.forEach((input) => input.addEventListener('change', updateEstimate));

            updateEstimate();
        })();
    </script>
</body>

</html>
