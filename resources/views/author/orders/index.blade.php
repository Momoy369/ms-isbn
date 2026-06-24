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
                            <label>ID Kota Tujuan RajaOngkir</label>
                            <input type="text" name="destination_city_id" class="form-control" placeholder="contoh: 39"
                                required>
                        </div>
                        <div class="form-row">
                            <div class="col form-group">
                                <label>Provinsi</label>
                                <input type="text" name="destination_province" class="form-control" required>
                            </div>
                            <div class="col form-group">
                                <label>Kota</label>
                                <input type="text" name="destination_city" class="form-control" required>
                            </div>
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
@endsection
