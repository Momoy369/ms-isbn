@extends('adminlte::page')

@section('title', 'Finance POS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Finance POS (Point of Sale)</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@endsection

@section('content')
    @foreach (['success', 'warning', 'info', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger rounded shadow-sm">
            <div class="font-weight-bold mb-1">Validasi gagal:</div>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-2">
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-primary shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ number_format($stats['orders_count']) }}</h3>
                    <p>Total Order POS</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-info shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['revenue_total'], 0, ',', '.') }}</h3>
                    <p>Total Nilai Order</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-warning shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['invoice_pending'], 0, ',', '.') }}</h3>
                    <p>Invoice POS Pending</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="small-box bg-success shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['invoice_paid'], 0, ',', '.') }}</h3>
                    <p>Invoice POS Lunas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-1"></i> Dashboard Ringkas Invoice POS</h3>
        </div>
        <div class="card-body py-2">
            <div class="row text-center">
                <div class="col-md-3 py-2">
                    <div class="small text-muted">Overdue Invoice</div>
                    <div class="h5 mb-0 text-danger">{{ number_format($stats['overdue_count']) }}</div>
                </div>
                <div class="col-md-3 py-2">
                    <div class="small text-muted">Pending Termin 1</div>
                    <div class="h5 mb-0">Rp {{ number_format($stats['pending_term_1'], 0, ',', '.') }}</div>
                </div>
                <div class="col-md-3 py-2">
                    <div class="small text-muted">Pending Termin 2</div>
                    <div class="h5 mb-0">Rp {{ number_format($stats['pending_term_2'], 0, ',', '.') }}</div>
                </div>
                <div class="col-md-3 py-2">
                    <div class="small text-muted">Persentase Invoice Lunas</div>
                    <div class="h5 mb-0 text-success">{{ number_format($stats['invoice_paid_ratio'], 1, ',', '.') }}%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Input Order POS Eksternal</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('finance.pos.orders.store') }}" id="pos-order-form" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Nama Customer</label>
                        <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">No. HP</label>
                        <input type="text" class="form-control" name="customer_phone" value="{{ old('customer_phone') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Email</label>
                        <input type="email" class="form-control" name="customer_email" value="{{ old('customer_email') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Sumber</label>
                        <select name="source_channel" class="form-control">
                            @foreach (['offline', 'whatsapp', 'instagram', 'marketplace', 'website', 'other'] as $channel)
                                <option value="{{ $channel }}" @selected(old('source_channel', 'offline') === $channel)>{{ strtoupper($channel) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Status</label>
                        <select name="status" class="form-control">
                            <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>Confirmed</option>
                            <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                            <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Judul Naskah (wajib jika ada Jasa Penerbitan)</label>
                        <input type="text" class="form-control" name="manuscript_title" value="{{ old('manuscript_title') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">No KTP Penulis</label>
                        <input type="text" class="form-control" name="author_ktp_number" value="{{ old('author_ktp_number') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">No Ref Jasa Terbit</label>
                        <input type="text" class="form-control" name="service_order_ref" value="{{ old('service_order_ref') }}" placeholder="REF-MKT-001">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Ref Marketing</label>
                        <input type="text" class="form-control" name="marketing_ref" value="{{ old('marketing_ref') }}" placeholder="Nama/Kode Marketing">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Mode Diskon</label>
                        <select name="discount_scope" class="form-control" id="pos-discount-scope">
                            <option value="global" @selected(old('discount_scope', 'global') === 'global')>Global</option>
                            <option value="unit" @selected(old('discount_scope') === 'unit')>Satuan (Per Qty)</option>
                            <option value="item" @selected(old('discount_scope') === 'item')>Per Item</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Tipe Diskon</label>
                        <select name="discount_type" class="form-control" id="pos-discount-type">
                            <option value="nominal" @selected(old('discount_type', 'nominal') === 'nominal')>Nominal (Rp)</option>
                            <option value="percent" @selected(old('discount_type') === 'percent')>Persen (%)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Diskon</label>
                        <input type="number" min="0" step="100" name="discount_amount" class="form-control" id="pos-discount" value="{{ old('discount_amount', 0) }}">
                    </div>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered" id="pos-items-table">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 14%;">Jenis Item</th>
                                <th style="width: 16%;">Paket (Jasa)</th>
                                <th style="width: 22%;">Produk Existing (Buku/Ebook)</th>
                                <th style="width: 16%;">Nama Item</th>
                                <th style="width: 8%;">Qty</th>
                                <th style="width: 12%;">Harga Satuan</th>
                                <th style="width: 10%;">Tipe Diskon Item</th>
                                <th style="width: 10%;">Diskon Item</th>
                                <th style="width: 12%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="pos-item-row">
                                <td>
                                    <select name="items[0][item_type]" class="form-control form-control-sm pos-item-type" required>
                                        <option value="publishing_service">Jasa Penerbitan</option>
                                        <option value="book_print">Buku</option>
                                        <option value="ebook">Ebook</option>
                                        <option value="extra_service">Layanan Extra</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][publishing_package_id]" class="form-control form-control-sm pos-package-select">
                                        <option value="">- Pilih Paket -</option>
                                        @foreach ($packages as $pkg)
                                            <option value="{{ $pkg->id }}" data-price="{{ (float) $pkg->price }}">{{ $pkg->name }} (Rp {{ number_format($pkg->price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm pos-product-select">
                                        <option value="">- Produk Otomatis -</option>
                                        @foreach ($productOptions as $opt)
                                            <option value="{{ $opt['source_type'] }}|{{ $opt['source_id'] }}|{{ $opt['item_type'] }}" data-source-type="{{ $opt['source_type'] }}" data-source-id="{{ $opt['source_id'] }}" data-item-type="{{ $opt['item_type'] }}" data-name="{{ $opt['name'] }}" data-price="{{ $opt['price'] }}">{{ $opt['name'] }} - Rp {{ number_format($opt['price'], 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[0][product_source_type]" class="pos-product-source-type">
                                    <input type="hidden" name="items[0][product_source_id]" class="pos-product-source-id">
                                </td>
                                <td>
                                    <input type="text" name="items[0][item_name]" class="form-control form-control-sm pos-item-name">
                                </td>
                                <td>
                                    <input type="number" min="1" step="1" name="items[0][quantity]" class="form-control form-control-sm pos-qty" value="1" required>
                                </td>
                                <td>
                                    <input type="number" min="0" step="100" name="items[0][unit_price]" class="form-control form-control-sm pos-price" value="0">
                                </td>
                                <td>
                                    <select name="items[0][discount_type]" class="form-control form-control-sm pos-item-discount-type">
                                        <option value="nominal">Nominal</option>
                                        <option value="percent">Persen</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="0" step="100" name="items[0][discount_amount]" class="form-control form-control-sm pos-item-discount" value="0">
                                </td>
                                <td class="text-right align-middle pos-line-total">Rp 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4 mb-2">
                        <button type="button" class="btn btn-outline-primary" id="add-item-row"><i class="fas fa-plus mr-1"></i> Tambah Item</button>
                        <button type="button" class="btn btn-outline-info ml-1" data-toggle="modal" data-target="#publishing-detail-modal" id="open-publishing-detail" disabled>
                            <i class="fas fa-file-alt mr-1"></i> Detail Jasa Penerbitan
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" id="pos-subtotal-preview" value="Subtotal: Rp 0" readonly>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control font-weight-bold" id="pos-total-preview" value="Total: Rp 0" readonly>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="small text-muted mb-1">Catatan Order</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Order POS</button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="publishing-detail-modal" tabindex="-1" role="dialog" aria-labelledby="publishing-detail-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="publishing-detail-modal-label">Detail Jasa Penerbitan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">Isi detail ini jika order mengandung item <strong>Jasa Penerbitan</strong>. Sistem menghitung biaya lebih halaman otomatis sesuai settings dinamis.</div>
                    <div class="form-row">
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Rule Ukuran Buku (Master Harga Cetak)</label>
                            <select name="publishing_detail_print_price_rule_id" id="publishing-detail-rule" class="form-control" form="pos-order-form">
                                <option value="">- Default (A5) -</option>
                                @foreach ($printRules as $rule)
                                    <option value="{{ $rule->id }}">{{ $rule->name }} @if ($rule->paper_size)[{{ strtoupper($rule->paper_size) }}]@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Upload DOCX Naskah (opsional auto-hitungan)</label>
                            <input type="file" name="publishing_detail_manuscript_file" id="publishing-detail-file" class="form-control-file" accept=".docx" form="pos-order-form">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Manual Halaman A4 (fallback)</label>
                            <input type="number" min="1" step="1" name="publishing_detail_manual_a4_pages" id="publishing-detail-a4" class="form-control" value="1" form="pos-order-form">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Manual Halaman A5 (fallback)</label>
                            <input type="number" min="1" step="1" name="publishing_detail_manual_a5_pages" id="publishing-detail-a5" class="form-control" value="1" form="pos-order-form">
                        </div>
                    </div>
                    <div class="alert alert-info mb-0" id="publishing-detail-preview">Klik tombol Hitung Preview untuk melihat estimasi biaya lebih halaman.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" id="publishing-detail-preview-btn">Hitung Preview</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Simpan Detail</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filter Order POS</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-2"><input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari order/customer/ref..."></div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="source_channel" class="form-control">
                        <option value="">Semua Sumber</option>
                        @foreach (['offline', 'whatsapp', 'instagram', 'marketplace', 'website', 'other'] as $channel)
                            <option value="{{ $channel }}" @selected(request('source_channel') === $channel)>{{ strtoupper($channel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search mr-1"></i> Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Daftar Order POS & Invoice Termin</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th class="text-right">Nilai Order</th>
                            <th>Invoice Termin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <code>{{ $order->order_number }}</code>
                                    <div class="small text-muted mt-1">{{ strtoupper($order->source_channel) }}</div>
                                    <div class="small mt-1"><strong>Ref Jasa:</strong> {{ $order->service_order_ref ?: '-' }}</div>
                                    <div class="small"><strong>Marketing:</strong> {{ $order->marketing_ref ?: '-' }}</div>
                                    @if (!empty($order->publishing_metadata))
                                        <div class="small mt-1 text-muted">{{ strtoupper((string) ($order->publishing_metadata['selected_print_paper'] ?? 'A5')) }}: {{ (int) ($order->publishing_metadata['selected_print_pages'] ?? 0) }} hal, lebih: {{ (int) ($order->publishing_metadata['print_over_pages'] ?? 0) }} hal</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $order->customer_name }}</div>
                                    <div class="small text-muted">{{ $order->customer_phone ?: '-' }}</div>
                                    <div class="small text-muted">{{ $order->customer_email ?: '-' }}</div>
                                </td>
                                <td>
                                    @foreach ($order->items as $item)
                                        <div class="small mb-1 border-bottom pb-1">
                                            <span class="badge badge-light mr-1">{{ strtoupper(str_replace('_', ' ', $item->item_type)) }}</span>
                                            {{ $item->item_name }}
                                            <div class="text-muted">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="text-right">
                                    <div class="font-weight-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                    <div class="small text-muted">Diskon: Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</div>
                                </td>
                                <td style="min-width:340px;">
                                    @forelse ($order->invoices as $inv)
                                        <div class="border rounded p-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <code>{{ $inv->invoice_number }}</code>
                                                <span class="badge badge-{{ $inv->getStatusBadgeColor() }}">{{ strtoupper($inv->status) }}</span>
                                            </div>
                                            <div class="small">Termin {{ $inv->installment_number }} - Rp {{ number_format($inv->amount, 0, ',', '.') }}</div>
                                            <a class="btn btn-xs btn-outline-secondary mt-1" href="{{ route('finance.pos.invoices.pdf', $inv) }}"><i class="fas fa-file-pdf"></i> PDF</a>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Belum ada invoice termin.</span>
                                    @endforelse
                                </td>
                                <td style="min-width:280px;">
                                    <form method="POST" action="{{ route('finance.pos.invoices.store', $order) }}" class="border rounded p-2 mb-2">
                                        @csrf
                                        <div class="form-row">
                                            <div class="col-4 mb-1">
                                                <select name="installment_number" class="form-control form-control-sm">
                                                    <option value="1">Termin 1</option>
                                                    <option value="2">Termin 2</option>
                                                </select>
                                            </div>
                                            <div class="col-8 mb-1"><input type="number" min="0" step="100" name="amount" class="form-control form-control-sm" placeholder="Nominal" required></div>
                                            <div class="col-12"><button class="btn btn-xs btn-primary" type="submit">Buat Invoice</button></div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada order POS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer bg-white">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection

@section('js')
    <script>
        (function() {
            const tableBody = document.querySelector('#pos-items-table tbody');
            const addBtn = document.getElementById('add-item-row');
            const openPublishingDetailBtn = document.getElementById('open-publishing-detail');
            const discountScopeInput = document.getElementById('pos-discount-scope');
            const discountInput = document.getElementById('pos-discount');
            const discountTypeInput = document.getElementById('pos-discount-type');
            const subtotalPreview = document.getElementById('pos-subtotal-preview');
            const totalPreview = document.getElementById('pos-total-preview');

            const previewBtn = document.getElementById('publishing-detail-preview-btn');
            const previewBox = document.getElementById('publishing-detail-preview');
            const previewRule = document.getElementById('publishing-detail-rule');
            const previewFile = document.getElementById('publishing-detail-file');
            const previewA4 = document.getElementById('publishing-detail-a4');
            const previewA5 = document.getElementById('publishing-detail-a5');

            function formatRupiah(value) {
                const number = Number(value) || 0;
                return 'Rp ' + number.toLocaleString('id-ID');
            }

            function refreshRowIndexes() {
                const rows = tableBody.querySelectorAll('.pos-item-row');
                rows.forEach((row, idx) => {
                    row.querySelectorAll('input, select').forEach((el) => {
                        const name = el.getAttribute('name');
                        if (!name) return;
                        el.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + idx + ']'));
                    });
                });
            }

            function applyRules(row) {
                const itemType = row.querySelector('.pos-item-type')?.value || '';
                const packageSelect = row.querySelector('.pos-package-select');
                const productSelect = row.querySelector('.pos-product-select');
                const nameInput = row.querySelector('.pos-item-name');
                const priceInput = row.querySelector('.pos-price');

                const isPublishing = itemType === 'publishing_service';
                const isCatalogType = itemType === 'book_print' || itemType === 'ebook';

                if (packageSelect) packageSelect.disabled = !isPublishing;
                if (productSelect) productSelect.disabled = !isCatalogType;

                if (isPublishing && packageSelect) {
                    const opt = packageSelect.options[packageSelect.selectedIndex];
                    const packagePrice = Number(opt?.getAttribute('data-price') || 0);
                    if (nameInput) nameInput.value = opt && opt.value ? 'Jasa Penerbitan - ' + opt.text.replace(/\s*\(Rp.*$/, '') : '';
                    if (priceInput) {
                        priceInput.value = packagePrice;
                        priceInput.readOnly = true;
                    }
                    if (productSelect) productSelect.selectedIndex = 0;
                    return;
                }

                if (priceInput) priceInput.readOnly = false;
            }

            function recalcTotals() {
                let subtotal = 0;
                let discount = 0;
                const discountScope = discountScopeInput?.value || 'global';
                const discountType = discountTypeInput?.value || 'nominal';
                const rawDiscount = Math.max(0, Number(discountInput?.value || 0));

                tableBody.querySelectorAll('.pos-item-row').forEach((row) => {
                    const qty = Number(row.querySelector('.pos-qty')?.value || 0);
                    const price = Number(row.querySelector('.pos-price')?.value || 0);
                    const lineTotal = Math.max(0, qty * price);
                    subtotal += lineTotal;

                    let lineDiscount = 0;
                    if (discountScope === 'item') {
                        const itemDiscountType = row.querySelector('.pos-item-discount-type')?.value || 'nominal';
                        const itemDiscountRaw = Math.max(0, Number(row.querySelector('.pos-item-discount')?.value || 0));
                        lineDiscount = itemDiscountType === 'percent' ? Math.min(100, itemDiscountRaw) * lineTotal / 100 : Math.min(lineTotal, itemDiscountRaw);
                    } else if (discountScope === 'unit') {
                        lineDiscount = discountType === 'percent' ? (Math.min(100, rawDiscount) * price / 100) * qty : Math.min(price, rawDiscount) * qty;
                        lineDiscount = Math.min(lineTotal, lineDiscount);
                    }

                    discount += lineDiscount;
                    const line = row.querySelector('.pos-line-total');
                    if (line) line.textContent = formatRupiah(lineTotal);
                });

                if (discountScope === 'global') {
                    discount = discountType === 'percent' ? Math.min(100, rawDiscount) * subtotal / 100 : Math.min(subtotal, rawDiscount);
                }

                discount = Math.min(subtotal, Math.max(0, discount));
                const total = Math.max(0, subtotal - discount);
                subtotalPreview.value = 'Subtotal: ' + formatRupiah(subtotal);
                totalPreview.value = 'Total: ' + formatRupiah(total) + ' (Diskon: ' + formatRupiah(discount) + ')';
            }

            function refreshDiscountInputMeta() {
                const discountType = discountTypeInput?.value || 'nominal';
                const discountScope = discountScopeInput?.value || 'global';

                if (discountInput) {
                    const useGlobalInput = discountScope !== 'item';
                    discountInput.disabled = !useGlobalInput;
                    if (discountTypeInput) discountTypeInput.disabled = !useGlobalInput;

                    if (discountType === 'percent') {
                        discountInput.step = '0.01';
                        discountInput.max = '100';
                    } else {
                        discountInput.step = '100';
                        discountInput.removeAttribute('max');
                    }
                }

                tableBody.querySelectorAll('.pos-item-row').forEach((row) => {
                    const itemTypeEl = row.querySelector('.pos-item-discount-type');
                    const itemAmountEl = row.querySelector('.pos-item-discount');
                    const enabled = discountScope === 'item';
                    if (itemTypeEl) itemTypeEl.disabled = !enabled;
                    if (itemAmountEl) {
                        itemAmountEl.disabled = !enabled;
                        const t = itemTypeEl?.value || 'nominal';
                        if (t === 'percent') {
                            itemAmountEl.step = '0.01';
                            itemAmountEl.max = '100';
                        } else {
                            itemAmountEl.step = '100';
                            itemAmountEl.removeAttribute('max');
                        }
                    }
                });
            }

            function bindRow(row) {
                row.querySelectorAll('.pos-item-type, .pos-package-select, .pos-product-select').forEach((el) => {
                    el.addEventListener('change', function() {
                        applyRules(row);
                        refreshPublishingDetailButton();
                        recalcTotals();
                    });
                });
                row.querySelectorAll('.pos-price, .pos-qty, .pos-item-discount').forEach((el) => el.addEventListener('input', recalcTotals));
                row.querySelectorAll('.pos-item-discount-type').forEach((el) => {
                    el.addEventListener('change', function() {
                        refreshDiscountInputMeta();
                        recalcTotals();
                    });
                });
                applyRules(row);
            }

            function refreshPublishingDetailButton() {
                if (!openPublishingDetailBtn) return;
                const hasPublishing = Array.from(tableBody.querySelectorAll('.pos-item-type')).some((el) => (el.value || '') === 'publishing_service');
                openPublishingDetailBtn.disabled = !hasPublishing;
            }

            function getFirstPublishingPackageId() {
                const rows = tableBody.querySelectorAll('.pos-item-row');
                for (const row of rows) {
                    const type = row.querySelector('.pos-item-type')?.value || '';
                    if (type !== 'publishing_service') continue;
                    const pkg = row.querySelector('.pos-package-select')?.value || '';
                    if (pkg) return pkg;
                }
                return '';
            }

            previewBtn?.addEventListener('click', async function() {
                const packageId = getFirstPublishingPackageId();
                if (!packageId) {
                    previewBox.className = 'alert alert-warning mb-0';
                    previewBox.textContent = 'Pilih item Jasa Penerbitan dan paket terlebih dahulu.';
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('publishing_package_id', packageId);
                formData.append('publishing_detail_print_price_rule_id', previewRule?.value || '');
                formData.append('publishing_detail_manual_a4_pages', previewA4?.value || '1');
                formData.append('publishing_detail_manual_a5_pages', previewA5?.value || '1');
                if (previewFile?.files && previewFile.files[0]) {
                    formData.append('publishing_detail_manuscript_file', previewFile.files[0]);
                }

                previewBox.className = 'alert alert-info mb-0';
                previewBox.textContent = 'Menghitung preview...';

                try {
                    const resp = await fetch('{{ route('finance.pos.overage.preview') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const payload = await resp.json();
                    if (!resp.ok || !payload || payload.ok !== true) {
                        throw new Error(payload?.message || 'Gagal menghitung preview.');
                    }

                    const d = payload.data || {};
                    previewBox.className = 'alert alert-success mb-0';
                    previewBox.innerHTML = 'A4: <strong>' + (d.a4_pages || 0) + '</strong> (lebih: ' + (d.a4_over_pages || 0) + ') | ' +
                        String(d.selected_print_paper || 'A5') + ': <strong>' + (d.selected_print_pages || 0) + '</strong> (lebih: ' + (d.print_over_pages || 0) + ')<br>' +
                        'Layout: <strong>' + formatRupiah(d.layout_fee || 0) + '</strong>, Editing: <strong>' + formatRupiah(d.editing_fee || 0) + '</strong>, Cetak: <strong>' + formatRupiah(d.print_fee || 0) + '</strong>, Total extra: <strong>' + formatRupiah(d.extra_fee || 0) + '</strong>';
                } catch (err) {
                    previewBox.className = 'alert alert-danger mb-0';
                    previewBox.textContent = err.message || 'Gagal menghitung preview.';
                }
            });

            addBtn?.addEventListener('click', function() {
                const firstRow = tableBody.querySelector('.pos-item-row');
                if (!firstRow) return;
                const clone = firstRow.cloneNode(true);
                clone.querySelectorAll('input').forEach((el) => {
                    if (el.classList.contains('pos-qty')) {
                        el.value = '1';
                    } else {
                        el.value = '0';
                    }
                    if (el.classList.contains('pos-item-name')) el.value = '';
                });
                clone.querySelectorAll('select').forEach((el) => {
                    el.selectedIndex = 0;
                });
                tableBody.appendChild(clone);
                refreshRowIndexes();
                bindRow(clone);
                refreshPublishingDetailButton();
                refreshDiscountInputMeta();
                recalcTotals();
            });

            discountInput?.addEventListener('input', recalcTotals);
            discountTypeInput?.addEventListener('change', function() { refreshDiscountInputMeta(); recalcTotals(); });
            discountScopeInput?.addEventListener('change', function() { refreshDiscountInputMeta(); recalcTotals(); });

            tableBody.querySelectorAll('.pos-item-row').forEach(bindRow);
            refreshPublishingDetailButton();
            refreshDiscountInputMeta();
            recalcTotals();
        })();
    </script>
@endsection
