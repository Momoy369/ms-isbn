@extends('adminlte::page')

@section('title', 'Penjualan Eksternal')

@section('content_header')
    <h1 class="m-0">Input Penjualan Eksternal</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible rounded">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($totals['gross'], 0, ',', '.') }}</h3>
                    <p>Total Omzet Tercatat</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($totals['royalty'], 0, ',', '.') }}</h3>
                    <p>Total Royalti Author (Rate per Buku)</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    <div class="card mb-3" id="royalty-program">
        <div class="card-header"><strong>Kurasi Buku Program Distribusi & Royalti</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('external-sales.royalty-program') }}" enctype="multipart/form-data"
                class="row">
                @csrf
                <div class="col-md-4 form-group">
                    <label>Buku Selesai</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">- Pilih Buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">
                                {{ $book->judul }} ({{ $book->nomor_naskah }})
                                @if ($book->royalty_enabled)
                                    - Aktif
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hanya buku status selesai/ISBN approved yang ditampilkan.</small>
                </div>

                <div class="col-md-2 form-group">
                    <label>Aktifkan Program</label>
                    <select name="royalty_enabled" class="form-control" required>
                        <option value="1">Ya</option>
                        <option value="0">Tidak</option>
                    </select>
                </div>

                <div class="col-md-2 form-group">
                    <label>Rate Royalti</label>
                    <input type="number" step="0.0001" min="0" max="1" name="royalty_rate"
                        class="form-control" placeholder="0.2000">
                    <small class="text-muted">Kosongkan untuk default 20%.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label>Catatan Kurasi</label>
                    <input type="text" name="royalty_notes" class="form-control"
                        placeholder="Contoh: layak untuk distribusi marketplace + ebook">
                </div>

                <div class="col-md-2 form-group">
                    <label class="d-block">Distribusi Online</label>
                    <input type="hidden" name="royalty_distribution_online" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="dist-online"
                            name="royalty_distribution_online" value="1">
                        <label class="custom-control-label" for="dist-online">Aktif</label>
                    </div>
                </div>

                <div class="col-md-2 form-group">
                    <label class="d-block">Distribusi Ebook</label>
                    <input type="hidden" name="royalty_distribution_ebook" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="dist-ebook"
                            name="royalty_distribution_ebook" value="1">
                        <label class="custom-control-label" for="dist-ebook">Aktif</label>
                    </div>
                </div>

                <div class="col-md-2 form-group">
                    <label class="d-block">Marketplace</label>
                    <input type="hidden" name="royalty_distribution_marketplace" value="0">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="dist-marketplace"
                            name="royalty_distribution_marketplace" value="1">
                        <label class="custom-control-label" for="dist-marketplace">Aktif</label>
                    </div>
                </div>

                <div class="col-md-3 form-group">
                    <label>Upload Surat Perjanjian</label>
                    <input type="file" name="royalty_agreement_file" class="form-control" accept=".pdf,.doc,.docx">
                </div>

                <div class="col-md-3 form-group">
                    <label>Upload Kontrak</label>
                    <input type="file" name="royalty_contract_file" class="form-control" accept=".pdf,.doc,.docx">
                </div>

                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Simpan Program</button>
                </div>
            </form>

            @if ($selectedBook)
                <div class="alert alert-info border mt-3 mb-0">
                    <strong>Buku terpilih:</strong> {{ $selectedBook->judul }} ({{ $selectedBook->nomor_naskah }})
                    <div class="small mb-0">Gunakan panel ini untuk menyalakan royalti, mengatur rate, dan mengunggah surat
                        perjanjian/kontrak.</div>
                </div>
            @endif

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Buku</th>
                            <th>Rate</th>
                            <th>Online</th>
                            <th>Ebook</th>
                            <th>Marketplace</th>
                            <th>Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eligibleBooks as $book)
                            <tr>
                                <td>{{ $book->judul }}</td>
                                <td>{{ number_format($book->royaltyRate() * 100, 2) }}%</td>
                                <td>{{ $book->royalty_distribution_online ? 'Ya' : 'Tidak' }}</td>
                                <td>{{ $book->royalty_distribution_ebook ? 'Ya' : 'Tidak' }}</td>
                                <td>{{ $book->royalty_distribution_marketplace ? 'Ya' : 'Tidak' }}</td>
                                <td>
                                    @if ($book->royalty_agreement_file_path)
                                        <span class="badge badge-info">Perjanjian</span>
                                    @endif
                                    @if ($book->royalty_contract_file_path)
                                        <span class="badge badge-success">Kontrak</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada buku yang diaktifkan untuk
                                    program royalti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Export CSV Periode</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('finance.export.sales') }}" class="form-row">
                <div class="col-md-3 form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-file-csv mr-1"></i> Export Sales CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Harga Jual Aktual Buku</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('external-sales.update-book-price') }}" class="form-row">
                @csrf
                <div class="col-md-4 form-group">
                    <label>Buku</label>
                    <select name="book_id" class="form-control selling-price-source" required>
                        <option value="">- Pilih Buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}"
                                data-selling-price="{{ (float) ($book->selling_price ?? 0) }}">
                                {{ $book->judul }} ({{ $book->nomor_naskah }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Harga Jual Aktual</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" min="0"
                        required>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-warning btn-block" type="submit">Update Harga</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Tambah Data Penjualan</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('external-sales.store') }}" class="row">
                @csrf
                <div class="col-md-4 form-group">
                    <label>Buku</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">- Pilih Buku Program Royalti -</option>
                        @foreach ($eligibleBooks as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }} ({{ $book->nomor_naskah }})</option>
                        @endforeach
                    </select>
                    @if ($eligibleBooks->isEmpty())
                        <small class="text-danger">Belum ada buku eligible. Aktifkan di panel kurasi di atas.</small>
                    @endif
                </div>
                <div class="col-md-2 form-group">
                    <label>Channel</label>
                    <select name="channel" class="form-control" required>
                        <option value="amazon">Amazon</option>
                        <option value="google_play_books">Google Play Books</option>
                        <option value="website">Website</option>
                        <option value="marketplace">Marketplace</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Format</label>
                    <select name="format" class="form-control" required>
                        <option value="ebook">Ebook</option>
                        <option value="print">Print</option>
                    </select>
                </div>
                <div class="col-md-1 form-group">
                    <label>Qty</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Harga Jual</label>
                    <input type="number" step="0.01" name="unit_price" id="unit-price-input" class="form-control"
                        min="0" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Gross (opsional)</label>
                    <input type="number" step="0.01" name="gross_amount" class="form-control" min="0">
                </div>
                <div class="col-md-2 form-group">
                    <label>Tanggal</label>
                    <input type="date" name="sold_at" class="form-control" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit" @disabled($eligibleBooks->isEmpty())>Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Riwayat Penjualan Eksternal</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Buku</th>
                        <th>Channel</th>
                        <th>Format</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Gross</th>
                        <th>Royalti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $row)
                        @php
                            $royaltyUnitPrice =
                                (float) (($row->book->selling_price ?? 0) > 0
                                    ? $row->book->selling_price
                                    : $row->unit_price);
                            $rowRoyalty =
                                ((int) $row->quantity) *
                                $royaltyUnitPrice *
                                (optional($row->book)->royaltyRate() ?? 0.2);
                        @endphp
                        <tr>
                            <td>{{ $row->sold_at->format('d M Y') }}</td>
                            <td>{{ $row->book->judul ?? '-' }}</td>
                            <td>{{ strtoupper(str_replace('_', ' ', $row->channel)) }}</td>
                            <td>{{ strtoupper($row->format) }}</td>
                            <td>{{ number_format($row->quantity) }}</td>
                            <td>Rp {{ number_format($row->unit_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row->gross_amount, 0, ',', '.') }}</td>
                            <td class="font-weight-bold text-success">Rp {{ number_format($rowRoyalty, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Belum ada data penjualan eksternal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="card-footer">{{ $records->links() }}</div>
        @endif
    </div>

    <script>
        (function() {
            const bookSelect = document.querySelector('.selling-price-source');
            const unitPriceInput = document.getElementById('unit-price-input');
            if (!bookSelect || !unitPriceInput) return;

            bookSelect.addEventListener('change', function() {
                const selected = bookSelect.options[bookSelect.selectedIndex];
                const sellingPrice = parseFloat(selected ? (selected.getAttribute('data-selling-price') ||
                    '0') : '0');

                if (!Number.isNaN(sellingPrice) && sellingPrice > 0) {
                    unitPriceInput.value = sellingPrice.toFixed(2);
                }
            });
        })();
    </script>
@endsection
