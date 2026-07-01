@extends('adminlte::page')

@section('title', 'Ebook Publishing Workspace')

@section('content_header')
    <h1 class="m-0">Ebook Publishing Workspace</h1>
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
                    <p>Siap Publikasi Ebook</p>
                </div>
                <div class="icon"><i class="fas fa-money-check"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-danger mb-0">
                <div class="inner">
                    <h3>{{ $stats['revision'] }}</h3>
                    <p>Perlu Revisi Ebook</p>
                </div>
                <div class="icon"><i class="fas fa-edit"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>{{ $stats['published'] }}</h3>
                    <p>Ebook Sudah Published</p>
                </div>
                <div class="icon"><i class="fas fa-globe"></i></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Cari judul buku / author ...">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach (['invoiced', 'paid', 'ebook_revision_requested', 'ebook_publishing', 'ebook_published', 'cancelled'] as $status)
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
        <div class="card-header"><strong>Daftar Order Ebook Publishing</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Buku</th>
                        <th>Author</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $order->title ?? (optional($order->book)->judul ?? '-') }}</td>
                            <td>{{ optional($order->user)->name ?? '-' }}</td>
                            <td>{{ $order->ebook_platform ?? '-' }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($order->status) }}</span></td>
                            <td>
                                <a href="{{ route('ebook.workspace.show', $order) }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    Detail & Komunikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada order ebook publishing.</td>
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
