@extends('adminlte::page')

@section('title', 'Invoice Saya')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Invoice &amp; Tagihan</h1>
        <a href="{{ route('author.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
        </a>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible rounded">
            <button class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    {{-- Ringkasan saldo --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>Rp {{ number_format($totalPending, 0, ',', '.') }}</h3>
                    <p>Tagihan Belum Lunas</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
                    <p>Total Sudah Dibayar</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>Rp {{ number_format($totalPending + $totalPaid, 0, ',', '.') }}</h3>
                    <p>Total Kewajiban</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Semua Invoice</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No. Invoice</th>
                            <th>Naskah</th>
                            <th>Jenis</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Jumlah</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td><code>{{ $inv->invoice_number }}</code></td>
                                <td>{{ Str::limit($inv->book->judul ?? '-', 28) }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $inv->type === 'package' ? 'primary' : ($inv->type === 'revision' ? 'danger' : 'info') }}">
                                        {{ $inv->getTypeLabel() }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($inv->description, 38) }}</td>
                                <td class="text-right font-weight-bold">
                                    Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if ($inv->due_date)
                                        <span
                                            class="{{ $inv->isPending() && $inv->due_date->isPast() ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $inv->due_date->format('d M Y') }}
                                            @if ($inv->isPending() && $inv->due_date->isPast())
                                                <br><small>Jatuh tempo!</small>
                                            @endif
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $inv->getStatusBadgeColor() }}">
                                        {{ $inv->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('author.invoices.show', $inv) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                    Belum ada invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($invoices->hasPages())
            <div class="card-footer bg-white">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
