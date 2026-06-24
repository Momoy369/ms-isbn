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
                    <p>Total Royalti Author (20%)</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
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
                        <option value="">- Pilih Buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }} ({{ $book->nomor_naskah }})</option>
                        @endforeach
                    </select>
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
                    <input type="number" step="0.01" name="unit_price" class="form-control" min="0" required>
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
                    <button class="btn btn-primary btn-block" type="submit">Simpan</button>
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
                        <th>Royalti 20%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $row)
                        <tr>
                            <td>{{ $row->sold_at->format('d M Y') }}</td>
                            <td>{{ $row->book->judul ?? '-' }}</td>
                            <td>{{ strtoupper(str_replace('_', ' ', $row->channel)) }}</td>
                            <td>{{ strtoupper($row->format) }}</td>
                            <td>{{ number_format($row->quantity) }}</td>
                            <td>Rp {{ number_format($row->unit_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row->gross_amount, 0, ',', '.') }}</td>
                            <td class="font-weight-bold text-success">Rp
                                {{ number_format($row->gross_amount * 0.2, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Belum ada data penjualan eksternal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="card-footer">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
