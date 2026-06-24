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

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Beli Paket Baru</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('author.orders.buy-package') }}">
                        @csrf
                        <div class="form-group">
                            <label>Judul Naskah Baru</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Paket</label>
                            <select name="publishing_package_id" class="form-control" required>
                                <option value="">- Pilih Paket -</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} - Rp
                                        {{ number_format($pkg->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
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
                            <select name="book_id" class="form-control" required>
                                <option value="">- Pilih Buku -</option>
                                @foreach ($completedBooks as $book)
                                    <option value="{{ $book->id }}">{{ $book->judul }}</option>
                                @endforeach
                            </select>
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
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $order->order_type === 'new_package' ? 'Paket Baru' : 'Cetak Ulang' }}</td>
                            <td>{{ $order->title ?? ($order->book->judul ?? '-') }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($order->status) }}</span></td>
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
                            <td colspan="6" class="text-center text-muted py-3">Belum ada order.</td>
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

            if (!prov || !city || !cityId) return;

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

                    city.innerHTML = '<option value="">- Pilih Kota -</option>';
                    rows.forEach(function(row) {
                        const opt = document.createElement('option');
                        opt.value = row.city_name || '';
                        opt.textContent = (row.type ? row.type + ' ' : '') + (row.city_name || '');
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
        })();
    </script>
@endsection
