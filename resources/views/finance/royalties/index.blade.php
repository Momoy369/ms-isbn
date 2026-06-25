@extends('adminlte::page')

@section('title', 'Royalti & Pencairan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Royalti & Pencairan</h1>
        <a href="{{ route('finance.invoices.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Finance Invoice
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

    <div class="row mb-3">
        <div class="col-md-3 mb-2">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['pending'], 0, ',', '.') }}</h3>
                    <p>Pending ({{ $stats['pending_count'] }})</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['approved'], 0, ',', '.') }}</h3>
                    <p>Approved ({{ $stats['approved_count'] }})</p>
                </div>
                <div class="icon"><i class="fas fa-stamp"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['paid'], 0, ',', '.') }}</h3>
                    <p>Dibayar ({{ $stats['paid_count'] }})</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="small-box bg-danger mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['rejected'], 0, ',', '.') }}</h3>
                    <p>Rejected ({{ $stats['rejected_count'] }})</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-1"></i> Ringkasan Bulanan Request</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="text-right">Total Request</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthly as $period => $amount)
                        <tr>
                            <td>{{ $period }}</td>
                            <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">Belum ada data bulanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Permintaan Pencairan</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Author</th>
                            <th>Nominal</th>
                            <th>Rekening</th>
                            <th>Status</th>
                            <th>Ledger</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td>{{ $request->requested_at?->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $request->author->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $request->author->email ?? '' }}</small>
                                </td>
                                <td class="font-weight-bold">Rp {{ number_format($request->amount, 0, ',', '.') }}</td>
                                <td>
                                    {{ $request->bank_name }}<br>
                                    <small class="text-muted">{{ $request->bank_account_holder }} -
                                        {{ $request->bank_account_number }}</small>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'info' : ($request->status === 'paid' ? 'success' : 'danger')) }}">
                                        {{ strtoupper($request->status) }}
                                    </span>
                                    @if ($request->approved_at)
                                        <div class="small text-muted mt-1">Approved:
                                            {{ $request->approved_at->format('d M Y H:i') }}</div>
                                    @endif
                                    @if ($request->processed_at)
                                        <div class="small text-muted mt-1">
                                            {{ $request->processed_at->format('d M Y H:i') }}</div>
                                    @endif
                                </td>
                                <td style="min-width:220px;">
                                    @forelse ($request->ledgers as $ledger)
                                        <div class="small mb-1">
                                            <strong>{{ $ledger->book->judul ?? '-' }}</strong>
                                            <span class="text-muted">({{ $ledger->period_start->format('M Y') }})</span>
                                            <span class="badge badge-light border">Rp
                                                {{ number_format($ledger->net_royalty_amount ?? $ledger->royalty_amount, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($request->status === 'pending')
                                        <form method="POST" action="{{ route('finance.royalties.approve', $request) }}"
                                            class="mb-1">
                                            @csrf
                                            <input type="text" name="approval_notes"
                                                class="form-control form-control-sm mb-1"
                                                placeholder="Catatan approval (opsional)">
                                            <button class="btn btn-xs btn-success" type="submit"
                                                onclick="return confirm('Approve request ini?')">
                                                <i class="fas fa-stamp"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('finance.royalties.reject', $request) }}">
                                            @csrf
                                            <input type="text" name="rejection_reason"
                                                class="form-control form-control-sm mb-1"
                                                placeholder="Alasan reject (opsional)">
                                            <button class="btn btn-xs btn-danger" type="submit"
                                                onclick="return confirm('Tolak permintaan pencairan ini?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    @elseif ($request->status === 'approved')
                                        <form method="POST" action="{{ route('finance.royalties.pay', $request) }}"
                                            enctype="multipart/form-data" class="mb-1">
                                            @csrf
                                            <input type="text" name="payment_reference"
                                                class="form-control form-control-sm mb-1" placeholder="Ref transfer"
                                                required>
                                            <input type="file" name="transfer_proof"
                                                class="form-control form-control-sm mb-1" accept=".jpg,.jpeg,.png,.pdf">
                                            <button class="btn btn-xs btn-primary" type="submit"
                                                onclick="return confirm('Tandai request ini sebagai paid?')">
                                                <i class="fas fa-money-check-alt"></i> Mark Paid
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('finance.royalties.reject', $request) }}">
                                            @csrf
                                            <input type="text" name="rejection_reason"
                                                class="form-control form-control-sm mb-1"
                                                placeholder="Alasan reject (opsional)">
                                            <button class="btn btn-xs btn-danger" type="submit"
                                                onclick="return confirm('Batalkan approval dan tolak request ini?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada permintaan pencairan
                                    royalti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($requests->hasPages())
            <div class="card-footer bg-white">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
