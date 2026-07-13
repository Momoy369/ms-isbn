@extends('adminlte::page')

@section('title', 'Order Paket & Cetak Ulang')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Order Paket & Cetak Ulang</h1>
        <a href="{{ route('author.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>
@endsection

@section('content')
    <style>
        .order-stepper {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .order-step {
            font-size: 11px;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 3px 8px;
            color: #6b7280;
            background: #f9fafb;
        }

        .order-step.active {
            border-color: #0ea5e9;
            color: #0c4a6e;
            background: #e0f2fe;
            font-weight: 600;
        }

        .pv-chip {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .pv-chip.good {
            background: #dcfce7;
            color: #166534;
        }

        .pv-chip.warn {
            background: #fef3c7;
            color: #92400e;
        }

        .pv-chip.danger {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

    @foreach (['success', 'warning', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="alert alert-info">
        <strong>Akumulasi pembayaran Anda:</strong>
        Rp {{ number_format($accumulatedPayments, 0, ',', '.') }}
    </div>

    @if (!empty($provinceResult['is_fallback']))
        <div class="alert alert-warning">
            <strong>Info Ongkir:</strong>
            {{ $provinceResult['message'] ?? 'RajaOngkir sedang fallback ke data dummy.' }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Beli Paket Baru</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('author.orders.buy-package') }}" enctype="multipart/form-data"
                        id="buy-package-form">
                        @csrf
                        <div class="form-group">
                            <label>Judul Naskah Baru</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Upload Naskah (DOCX)</label>
                            <input type="file" name="manuscript_file" id="manuscript-file" class="form-control-file"
                                accept=".docx" required>
                            <small class="text-muted d-block mt-1">
                                Sistem menghitung halaman format A4 dan A5 (margin rata 2 cm) secara otomatis.
                            </small>
                            <small class="text-muted d-block">
                                Jika halaman A4 melebihi limit paket, biaya lebih layout dan editing dihitung otomatis
                                berdasarkan pengaturan dinamis admin.
                            </small>
                            <small class="text-muted d-block">
                                Jika paket termasuk cetak, limit dan biaya lebih halaman mengikuti ukuran buku pada
                                Master Harga Cetak + aturan dinamis di System Settings.
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Paket</label>
                            <select name="publishing_package_id" id="publishing-package-id" class="form-control" required>
                                <option value="">- Pilih Paket -</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }}
                                        [{{ $pkg->supports_print ? 'Cetak' : '' }}{{ $pkg->supports_print && $pkg->supports_ebook ? ' + ' : '' }}{{ $pkg->supports_ebook ? 'Ebook' : '' }}]
                                        - Rp
                                        {{ number_format($pkg->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ukuran Buku (Master Harga Cetak)</label>
                            <select name="package_print_price_rule_id" id="package-print-rule-id" class="form-control">
                                <option value="">- Default (A5) -</option>
                                @foreach ($printRules as $rule)
                                    <option value="{{ $rule->id }}">
                                        {{ $rule->name }}
                                        @if ($rule->paper_size)
                                            [{{ strtoupper($rule->paper_size) }}]
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Ukuran ini dipakai untuk limit halaman cetak dan biaya
                                lebih per halaman.</small>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <div id="package-preview-box" class="alert alert-light border d-none">
                            <div class="font-weight-bold mb-1">Estimasi Biaya Otomatis</div>
                            <div id="package-preview-loading" class="small text-muted d-none">Menghitung halaman dan
                                biaya...</div>
                            <div id="package-preview-error" class="small text-danger d-none"></div>
                            <div id="package-preview-content" class="small d-none">
                                <div id="pv-badges" class="mb-2"></div>
                                <div>A4: <span id="pv-a4-pages">0</span> hal (limit <span id="pv-a4-limit">125</span>) |
                                    Lebih: <span id="pv-a4-over">0</span> hal</div>
                                <div><span id="pv-selected-paper-label">A5</span>: <span id="pv-selected-pages">0</span> hal
                                    (limit cetak <span id="pv-selected-limit">100</span>)
                                    | Lebih cetak: <span id="pv-selected-over">0</span> hal</div>
                                <hr class="my-2">
                                <div>Biaya paket: <strong id="pv-package-price">Rp 0</strong></div>
                                <div>Biaya lebih layout: <strong id="pv-layout-fee">Rp 0</strong></div>
                                <div>Biaya lebih editing: <strong id="pv-editing-fee">Rp 0</strong></div>
                                <div>Biaya lebih cetak: <strong id="pv-print-fee">Rp 0</strong></div>
                                <div class="mt-1">Total estimasi: <strong id="pv-total">Rp 0</strong></div>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-shopping-bag mr-1"></i> Buat Order Paket
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Cetak Ulang Buku Selesai</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('author.orders.reprint') }}">
                        @csrf
                        <div class="form-group">
                            <label>Pilih Buku Selesai</label>
                            <select name="book_id" id="reprint-book-id" class="form-control" required>
                                <option value="">- Pilih Buku -</option>
                                @foreach ($completedBooks as $book)
                                    @php
                                        $pkg = $book->publishingPackage;
                                        $supportsPrint = $pkg ? (bool) $pkg->supports_print : true;
                                        $supportsEbook = $pkg ? (bool) $pkg->supports_ebook : false;
                                        $channel =
                                            $supportsPrint && $supportsEbook
                                                ? 'Cetak + Ebook'
                                                : ($supportsPrint
                                                    ? 'Cetak'
                                                    : 'Ebook-only');
                                    @endphp
                                    <option value="{{ $book->id }}"
                                        data-supports-print="{{ $supportsPrint ? '1' : '0' }}">
                                        {{ $book->judul }} [{{ $channel }}]
                                    </option>
                                @endforeach
                            </select>
                            <small id="reprint-adaptation-hint" class="text-warning d-none mt-1">
                                Buku ini berasal dari paket ebook-only. Order tetap bisa diproses cetak, dan akan ditandai
                                perlu penyesuaian naskah cetak di workspace percetakan.
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Rule Harga Cetak</label>
                            <select name="print_price_rule_id" class="form-control" required>
                                <option value="">- Pilih Rule -</option>
                                @foreach ($printRules as $rule)
                                    <option value="{{ $rule->id }}">{{ $rule->name }} ({{ $rule->paper_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Cetak</label>
                            <input type="number" name="quantity" min="1" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Provinsi Tujuan</label>
                            <select name="destination_province" id="destination-province" class="form-control" required>
                                <option value="">- Pilih Provinsi -</option>
                                @foreach ($provinces as $prov)
                                    <option value="{{ $prov['province'] ?? '' }}"
                                        data-id="{{ $prov['province_id'] ?? '' }}">
                                        {{ $prov['province'] ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kota Tujuan</label>
                            <select name="destination_city" id="destination-city" class="form-control" required>
                                <option value="">- Pilih Kota -</option>
                            </select>
                            <input type="hidden" name="destination_city_id" id="destination-city-id" required>
                        </div>
                        <div class="form-group">
                            <label>Kode Pos</label>
                            <input type="text" name="postal_code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="shipping_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Kurir</label>
                            <select name="courier" class="form-control" required>
                                <option value="jne">JNE</option>
                                <option value="tiki">TIKI</option>
                                <option value="pos">POS</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan untuk Tim Percetakan (opsional)</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Contoh: mohon penyesuaian margin dan ukuran trim untuk versi cetak."></textarea>
                        </div>
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-print mr-1"></i> Buat Order Cetak Ulang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Tambah Layanan Di Luar Paket</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('author.orders.service') }}" class="row">
                @csrf
                <div class="col-md-4 form-group">
                    <label>Layanan</label>
                    <select name="additional_service_id" class="form-control" required>
                        <option value="">- Pilih Layanan -</option>
                        @foreach ($additionalServices as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} - Rp
                                {{ number_format($service->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Buku (opsional)</label>
                    <select name="book_id" class="form-control">
                        <option value="">- Tidak terkait buku tertentu -</option>
                        @foreach ($completedBooks as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Qty</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-info btn-block" type="submit">Pesan Layanan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Riwayat Order</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Judul</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tracking Pengiriman</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if ($order->order_type === 'new_package')
                                    Paket Baru
                                @elseif ($order->order_type === 'ebook_publication')
                                    Ebook Publishing
                                @else
                                    Cetak Ulang
                                @endif
                            </td>
                            <td>{{ $order->title ?? ($order->book->judul ?? '-') }}</td>
                            <td>
                                <div>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                @if ($order->order_type === 'new_package' && (int) ($order->manuscript_a4_pages ?? 0) > 0)
                                    <div class="small text-muted">
                                        A4: {{ (int) $order->manuscript_a4_pages }} hal.
                                        @if ((int) ($order->over_limit_pages ?? 0) > 0)
                                            | Lebih {{ (int) $order->over_limit_pages }} hal.
                                            | Layout: Rp
                                            {{ number_format((float) ($order->layout_over_limit_fee ?? 0), 0, ',', '.') }}
                                            @if ((float) ($order->editing_over_limit_fee ?? 0) > 0)
                                                | Editing: Rp
                                                {{ number_format((float) ($order->editing_over_limit_fee ?? 0), 0, ',', '.') }}
                                            @endif
                                        @endif
                                        @if ((int) ($order->manuscript_a5_pages ?? 0) > 0)
                                            | {{ strtoupper(optional($order->printPriceRule)->paper_size ?: 'A5') }}:
                                            {{ (int) $order->manuscript_a5_pages }} hal.
                                        @endif
                                        @if ((int) ($order->print_over_limit_pages ?? 0) > 0)
                                            | Cetak lebih {{ (int) $order->print_over_limit_pages }} hal.
                                            | Cetak: Rp
                                            {{ number_format((float) ($order->print_over_limit_fee ?? 0), 0, ',', '.') }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-secondary mb-1">{{ strtoupper($order->status) }}</span>
                                @php
                                    $isEbookFlow = $order->order_type === 'ebook_publication';
                                    $printSteps = [
                                        'paid',
                                        'revision_requested',
                                        'printing',
                                        'print_completed',
                                        'shipping',
                                        'shipped',
                                        'delivered',
                                    ];
                                    $ebookSteps = [
                                        'paid',
                                        'ebook_revision_requested',
                                        'ebook_publishing',
                                        'ebook_published',
                                    ];
                                    $steps = $isEbookFlow ? $ebookSteps : $printSteps;
                                @endphp
                                <div class="order-stepper mt-1">
                                    @foreach ($steps as $step)
                                        <span class="order-step {{ $order->status === $step ? 'active' : '' }}">
                                            {{ strtoupper($step) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if ($order->order_type === 'ebook_publication')
                                    <div class="small">
                                        <div><strong>Platform:</strong> {{ $order->ebook_platform ?? '-' }}</div>
                                        <div><strong>Status:</strong> {{ strtoupper($order->status) }}</div>
                                        @if ($order->ebook_submitted_at)
                                            <div><strong>Submit:</strong>
                                                {{ $order->ebook_submitted_at->format('d M Y H:i') }}</div>
                                        @endif
                                        @if ($order->ebook_published_at)
                                            <div><strong>Published:</strong>
                                                {{ $order->ebook_published_at->format('d M Y H:i') }}</div>
                                        @endif
                                        @if ($order->ebook_publication_link)
                                            <div><a href="{{ $order->ebook_publication_link }}" target="_blank"
                                                    rel="noopener">Lihat Link Ebook</a></div>
                                        @endif
                                    </div>
                                @elseif ($order->order_type !== 'reprint')
                                    Tidak berlaku
                                @else
                                    <div class="small">
                                        <div><strong>Kurir:</strong> {{ $order->courier ?? '-' }}
                                            {{ $order->courier_service ?? '' }}</div>
                                        <div><strong>Resi:</strong> {{ $order->tracking_number ?? '-' }}</div>
                                        <div><strong>Posisi:</strong> {{ strtoupper($order->status) }}</div>
                                        @if ($order->shipping_notes)
                                            <div><strong>Catatan:</strong> {{ $order->shipping_notes }}</div>
                                        @endif
                                        @if ($order->shipped_at)
                                            <div><strong>Terkirim:</strong> {{ $order->shipped_at->format('d M Y H:i') }}
                                            </div>
                                        @endif
                                        @if ($order->delivered_at)
                                            <div><strong>Diterima:</strong> {{ $order->delivered_at->format('d M Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($order->invoice)
                                    <a href="{{ route('author.invoices.show', $order->invoice) }}"
                                        class="btn btn-xs btn-outline-primary">
                                        {{ $order->invoice->invoice_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>

    <script>
        (function() {
            const prov = document.getElementById('destination-province');
            const city = document.getElementById('destination-city');
            const cityId = document.getElementById('destination-city-id');
            const reprintBook = document.getElementById('reprint-book-id');
            const adaptationHint = document.getElementById('reprint-adaptation-hint');
            const packageSelect = document.getElementById('publishing-package-id');
            const packagePrintRuleSelect = document.getElementById('package-print-rule-id');
            const manuscriptFileInput = document.getElementById('manuscript-file');
            const previewBox = document.getElementById('package-preview-box');
            const previewLoading = document.getElementById('package-preview-loading');
            const previewError = document.getElementById('package-preview-error');
            const previewContent = document.getElementById('package-preview-content');
            const previewBadges = document.getElementById('pv-badges');

            const formatRupiah = (num) => {
                const val = Number(num || 0);
                return 'Rp ' + val.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });
            };

            const setPreviewText = (id, value) => {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = String(value);
                }
            };

            const hidePreview = () => {
                if (previewBox) previewBox.classList.add('d-none');
                if (previewLoading) previewLoading.classList.add('d-none');
                if (previewError) previewError.classList.add('d-none');
                if (previewContent) previewContent.classList.add('d-none');
            };

            const renderPreviewError = (message) => {
                if (!previewBox || !previewError || !previewLoading || !previewContent) {
                    return;
                }

                previewBox.classList.remove('d-none');
                previewLoading.classList.add('d-none');
                previewContent.classList.add('d-none');
                previewError.classList.remove('d-none');
                previewError.textContent = message;
            };

            let previewAbortController = null;

            const refreshPackagePreview = async () => {
                if (!packageSelect || !manuscriptFileInput || !previewBox || !previewLoading || !previewError ||
                    !previewContent) {
                    return;
                }

                const packageId = packageSelect.value;
                const file = manuscriptFileInput.files && manuscriptFileInput.files[0] ? manuscriptFileInput
                    .files[0] : null;

                if (!packageId || !file) {
                    hidePreview();
                    return;
                }

                if (previewAbortController) {
                    previewAbortController.abort();
                }

                previewAbortController = new AbortController();

                previewBox.classList.remove('d-none');
                previewLoading.classList.remove('d-none');
                previewError.classList.add('d-none');
                previewContent.classList.add('d-none');

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('publishing_package_id', packageId);
                formData.append('package_print_price_rule_id', packagePrintRuleSelect ? packagePrintRuleSelect
                    .value : '');
                formData.append('manuscript_file', file);

                try {
                    const response = await fetch('{{ route('author.orders.preview-package') }}', {
                        method: 'POST',
                        body: formData,
                        signal: previewAbortController.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload || payload.ok !== true) {
                        throw new Error((payload && payload.message) ? payload.message :
                            'Gagal memproses preview biaya.');
                    }

                    const data = payload.data || {};

                    setPreviewText('pv-a4-pages', data.a4_pages || 0);
                    setPreviewText('pv-a4-limit', data.a4_limit || 125);
                    setPreviewText('pv-a4-over', data.a4_over_pages || 0);
                    setPreviewText('pv-selected-paper-label', data.selected_print_paper || 'A5');
                    setPreviewText('pv-selected-pages', data.selected_print_pages || 0);
                    setPreviewText('pv-selected-limit', data.print_limit || 100);
                    setPreviewText('pv-selected-over', data.print_over_pages || 0);
                    setPreviewText('pv-package-price', formatRupiah(data.package_price || 0));
                    setPreviewText('pv-layout-fee', formatRupiah(data.layout_fee || 0));
                    setPreviewText('pv-editing-fee', formatRupiah(data.editing_fee || 0));
                    setPreviewText('pv-print-fee', formatRupiah(data.print_fee || 0));
                    setPreviewText('pv-total', formatRupiah(data.total || 0));

                    if (previewBadges) {
                        const badges = [];

                        badges.push(
                            `<span class="pv-chip ${Number(data.a4_over_pages || 0) > 0 ? 'danger' : 'good'}">A4 ${Number(data.a4_over_pages || 0) > 0 ? 'Melebihi Batas' : 'Aman'}</span>`
                        );

                        if (data.supports_print) {
                            badges.push(
                                `<span class="pv-chip ${Number(data.print_over_pages || 0) > 0 ? 'warn' : 'good'}">${String(data.selected_print_paper || 'A5')} Cetak ${Number(data.print_over_pages || 0) > 0 ? 'Melebihi Batas' : 'Aman'}</span>`
                            );
                        } else {
                            badges.push('<span class="pv-chip good">Paket Non-Cetak</span>');
                        }

                        if (!data.includes_editing) {
                            badges.push('<span class="pv-chip warn">Tanpa Editing</span>');
                        }

                        if (Number(data.extra_fee || 0) > 0) {
                            badges.push('<span class="pv-chip danger">Ada Biaya Tambahan</span>');
                        } else {
                            badges.push('<span class="pv-chip good">Tanpa Biaya Tambahan</span>');
                        }

                        previewBadges.innerHTML = badges.join('');
                    }

                    previewLoading.classList.add('d-none');
                    previewError.classList.add('d-none');
                    previewContent.classList.remove('d-none');
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    renderPreviewError(error.message || 'Gagal memproses preview biaya.');
                }
            };

            if (packageSelect && manuscriptFileInput) {
                packageSelect.addEventListener('change', refreshPackagePreview);
                manuscriptFileInput.addEventListener('change', refreshPackagePreview);
                packagePrintRuleSelect?.addEventListener('change', refreshPackagePreview);
            }

            if (prov && city && cityId) {
                prov.addEventListener('change', async function() {
                    city.innerHTML = '<option value="">Memuat kota...</option>';
                    cityId.value = '';

                    const selected = prov.options[prov.selectedIndex];
                    const provinceId = selected ? selected.getAttribute('data-id') : '';

                    if (!provinceId) {
                        city.innerHTML = '<option value="">- Pilih Kota -</option>';
                        return;
                    }

                    try {
                        const url = '{{ route('author.orders.cities') }}' + '?province_id=' +
                            encodeURIComponent(provinceId);
                        const resp = await fetch(url);
                        const json = await resp.json();
                        const rows = (json && json.data) ? json.data : [];

                        if (json && json.is_fallback) {
                            city.innerHTML = '<option value="">Data API fallback (dummy)</option>';
                        } else {
                            city.innerHTML = '<option value="">- Pilih Kota -</option>';
                        }

                        rows.forEach(function(row) {
                            const opt = document.createElement('option');
                            opt.value = row.city_name || '';
                            opt.textContent = (row.type ? row.type + ' ' : '') + (row.city_name ||
                                '');
                            opt.setAttribute('data-id', row.city_id || '');
                            city.appendChild(opt);
                        });
                    } catch (e) {
                        city.innerHTML = '<option value="">Gagal memuat kota</option>';
                    }
                });

                city.addEventListener('change', function() {
                    const selected = city.options[city.selectedIndex];
                    cityId.value = selected ? (selected.getAttribute('data-id') || '') : '';
                });
            }

            if (reprintBook && adaptationHint) {
                const renderAdaptationHint = function() {
                    const selected = reprintBook.options[reprintBook.selectedIndex];
                    const supportsPrint = selected ? selected.getAttribute('data-supports-print') : '1';

                    if (supportsPrint === '0') {
                        adaptationHint.classList.remove('d-none');
                    } else {
                        adaptationHint.classList.add('d-none');
                    }
                };

                reprintBook.addEventListener('change', renderAdaptationHint);
                renderAdaptationHint();
            }
        })();
    </script>
@endsection
