@extends('adminlte::page')

@section('title', 'Claim Buku')

@section('content_header')
    <h1 class="m-0">Claim Buku Berdasarkan KTP</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Status Profil Penulis</strong></div>
        <div class="card-body">
            <div>KTP akun: <strong>{{ $user->ktp_number ?? '-' }}</strong></div>
            <div>Nama KTP: <strong>{{ $user->ktp_name ?? '-' }}</strong></div>
            <div>Status kelengkapan:
                @if ($user->isAuthorProfileComplete())
                    <span class="badge badge-success">Lengkap</span>
                @else
                    <span class="badge badge-danger">Belum Lengkap</span>
                @endif
            </div>
            @if (!$user->isAuthorProfileComplete())
                <div class="alert alert-info mt-3 mb-0">
                    Lengkapi profil author melalui admin agar bisa mengajukan klaim buku.
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Buku yang Bisa Diklaim (KTP Cocok)</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No Naskah</th>
                            <th>Judul</th>
                            <th>Penulis (Input Admin)</th>
                            <th>KTP Buku</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claimableBooks as $book)
                            <tr>
                                <td>{{ $book->nomor_naskah }}</td>
                                <td>{{ $book->judul }}</td>
                                <td>{{ $book->penulis_1 }}</td>
                                <td>{{ $book->author_ktp_number }}</td>
                                <td>
                                    <form method="POST" action="{{ route('author.claims.store', $book) }}"
                                        class="d-inline">
                                        @csrf
                                        <input type="hidden" name="notes"
                                            value="Klaim via portal author berdasarkan kecocokan KTP.">
                                        <button class="btn btn-primary btn-sm"
                                            {{ !$user->isAuthorProfileComplete() ? 'disabled' : '' }}>
                                            Ajukan Klaim
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Tidak ada buku yang cocok dengan KTP
                                    akun Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Riwayat Klaim Saya</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Buku</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($myClaimRequests as $claim)
                            <tr>
                                <td>{{ $claim->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $claim->book->judul ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $claim->status === 'approved' ? 'success' : ($claim->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($claim->status) }}
                                    </span>
                                </td>
                                <td>{{ $claim->admin_notes ?? ($claim->notes ?? '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat klaim.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
