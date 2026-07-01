@extends('adminlte::page')

@section('title', 'Workspace Percetakan')

@section('content_header')
    <h1 class="m-0">Workspace Percetakan</h1>
@endsection

@section('content')
    @foreach (['success', 'warning', 'info', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded">
                <button class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>{{ $stats['invoiced'] }}</h3>
                    <p>Menunggu Pembayaran</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ $stats['paid'] }}</h3>
                    <p>Siap Diproses</p>
                </div>
                <div class="icon"><i class="fas fa-money-check"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-danger mb-0">
                <div class="inner">
                    <h3>{{ $stats['revision_requested'] }}</h3>
                    <p>Perlu Revisi</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>{{ $stats['printing'] }}</h3>
                    <p>Sedang Dicetak</p>
                </div>
                <div class="icon"><i class="fas fa-print"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 mb-2">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ $stats['shipping'] }}</h3>
                    <p>Dalam Pengiriman</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>{{ $stats['completed'] }}</h3>
                    <p>Cetak Selesai / Terkirim</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Cari judul buku / author ...">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach (['invoiced', 'paid', 'revision_requested', 'printing', 'print_completed', 'shipping', 'shipped', 'delivered', 'processing', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ strtoupper($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Order Cetak Ulang</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Buku</th>
                        <th>Author</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Tujuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                {{ $order->title ?? (optional($order->book)->judul ?? '-') }}
                                @if (str_contains((string) $order->notes, 'AUTO_PRINT_ADAPTATION_REQUIRED'))
                                    <div class="mt-1">
                                        <span class="badge badge-warning">Needs Print Adaptation</span>
                                    </div>
                                @endif
                                @if ($order->book)
                                    <div class="small">
                                        <a href="{{ route('books.show', $order->book) }}">Buka Detail Naskah</a>
                                    </div>
                                @endif
                            </td>
                            <td>{{ optional($order->user)->name ?? '-' }}</td>
                            <td>{{ number_format($order->quantity) }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>
                                {{ $order->destination_city ?? '-' }}, {{ $order->destination_province ?? '-' }}
                                <div class="small text-muted">{{ $order->courier ?? '-' }}
                                    {{ $order->courier_service ?? '' }}</div>
                            </td>
                            <td><span class="badge badge-secondary">{{ strtoupper($order->status) }}</span></td>
                            <td style="min-width:220px;">
                                <form method="POST" action="{{ route('printing.workspace.update-status', $order) }}">
                                    @csrf
                                    <div class="input-group input-group-sm mb-1">
                                        <select name="status" class="form-control">
                                            @foreach (['paid', 'revision_requested', 'printing', 'print_completed', 'shipping', 'shipped', 'delivered', 'processing', 'completed', 'cancelled'] as $st)
                                                <option value="{{ $st }}" @selected($order->status === $st)>
                                                    {{ strtoupper($st) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </div>
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                        value="{{ $order->notes }}" placeholder="Catatan tim percetakan...">
                                    <input type="text" name="tracking_number" class="form-control form-control-sm mt-1"
                                        value="{{ $order->tracking_number }}" placeholder="No Resi (jika sudah dikirim)">
                                    <input type="text" name="shipping_notes" class="form-control form-control-sm mt-1"
                                        value="{{ $order->shipping_notes }}" placeholder="Catatan pengiriman...">
                                </form>
                                <a href="{{ route('printing.workspace.show', $order) }}"
                                    class="btn btn-outline-secondary btn-sm mt-1">
                                    Detail, Revisi & Final Files
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Belum ada order cetak ulang.</td>
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
