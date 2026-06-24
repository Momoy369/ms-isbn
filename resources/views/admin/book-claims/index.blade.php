@extends('adminlte::page')

@section('title', 'Review Claim Buku')

@section('content_header')
    <h1 class="m-0">Review Claim Buku Penulis</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Buku</th>
                            <th>Penulis Buku</th>
                            <th>KTP Buku</th>
                            <th>Pengaju</th>
                            <th>KTP Pengaju</th>
                            <th>Status</th>
                            <th width="280">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claims as $claim)
                            <tr>
                                <td>{{ $claim->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div><strong>{{ $claim->book->judul ?? '-' }}</strong></div>
                                    <div class="small text-muted">{{ $claim->book->nomor_naskah ?? '-' }}</div>
                                </td>
                                <td>{{ $claim->book->penulis_1 ?? '-' }}</td>
                                <td>{{ $claim->book->author_ktp_number ?? '-' }}</td>
                                <td>
                                    <div><strong>{{ $claim->user->name ?? '-' }}</strong></div>
                                    <div class="small text-muted">{{ $claim->user->email ?? '-' }}</div>
                                </td>
                                <td>{{ $claim->ktp_number }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $claim->status === 'approved' ? 'success' : ($claim->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($claim->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($claim->status === 'pending')
                                        <form method="POST" action="{{ route('book-claims.approve', $claim) }}"
                                            class="mb-2">
                                            @csrf
                                            <input type="text" name="admin_notes"
                                                class="form-control form-control-sm mb-1"
                                                placeholder="Catatan admin (opsional)">
                                            <button class="btn btn-success btn-sm btn-block">Setujui Klaim</button>
                                        </form>
                                        <form method="POST" action="{{ route('book-claims.reject', $claim) }}">
                                            @csrf
                                            <textarea name="admin_notes" rows="2" class="form-control form-control-sm mb-1" placeholder="Alasan penolakan"
                                                required></textarea>
                                            <button class="btn btn-danger btn-sm btn-block">Tolak Klaim</button>
                                        </form>
                                    @else
                                        <div class="small text-muted">
                                            Diproses oleh: {{ optional($claim->reviewer)->name ?? '-' }}<br>
                                            {{ optional($claim->reviewed_at)->format('d M Y H:i') ?? '-' }}
                                        </div>
                                        <div class="small mt-1">{{ $claim->admin_notes ?? '-' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">Belum ada data klaim buku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
