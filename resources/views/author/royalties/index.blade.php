@extends('adminlte::page')

@section('title', 'Royalti Penulis')

@section('content_header')
    <h1 class="m-0">Royalti & Kontrak Distribusi</h1>
@endsection

@section('content')
    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($availablePayout, 0, ',', '.') }}</h3>
                    <p>Saldo Siap Dicairkan</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-md-8 d-flex align-items-stretch">
            <div class="card w-100 mb-0">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:.5rem;">
                    <div>
                        <div class="font-weight-bold">Export Laporan Royalti</div>
                        <div class="text-muted small">Unduh laporan per periode untuk semua buku yang diaktifkan admin.
                        </div>
                    </div>
                    <form method="GET" action="{{ route('author.royalties.export') }}" class="form-inline"
                        style="gap:.4rem;">
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            aria-label="Start date">
                        <input type="date" name="end_date" class="form-control form-control-sm" aria-label="End date">
                        <button class="btn btn-outline-primary btn-sm" type="submit">
                            <i class="fas fa-file-csv mr-1"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <div class="card h-100">
                <div class="card-header"><strong>Rincian Rekening Pencairan</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('author.royalties.bank.update') }}" class="row">
                        @csrf
                        <div class="col-md-6 form-group">
                            <label>Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control"
                                value="{{ auth()->user()->bank_name }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>No. Rekening</label>
                            <input type="text" name="bank_account_number" class="form-control"
                                value="{{ auth()->user()->bank_account_number }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_holder" class="form-control"
                                value="{{ auth()->user()->bank_account_holder }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Cabang / Keterangan</label>
                            <input type="text" name="bank_branch" class="form-control"
                                value="{{ auth()->user()->bank_branch }}">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Simpan Rekening</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100" id="payout-request">
                <div class="card-header"><strong>Ajukan Pencairan Royalti</strong></div>
                <div class="card-body">
                    <div class="alert alert-light border small">
                        Minimal pencairan Rp50.000. Permintaan akan menyalin data rekening yang tersimpan saat ini.
                    </div>
                    <form method="POST" action="{{ route('author.royalties.payout.request') }}" class="row">
                        @csrf
                        <div class="col-md-6 form-group">
                            <label>Nominal Pencairan</label>
                            <input type="number" name="amount" class="form-control" min="50000" step="0.01"
                                value="{{ old('amount', $availablePayout) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Keterangan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Opsional">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success" type="submit" @disabled($availablePayout < 50000)>
                                Ajukan Pencairan
                            </button>
                        </div>
                    </form>
                    @if ($availablePayout < 50000)
                        <small class="text-muted d-block mt-2">Saldo belum mencapai batas minimal pencairan.</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ $summary['books'] }}</h3>
                    <p>Buku Program Royalti</p>
                </div>
                <div class="icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($summary['gross'], 0, ',', '.') }}</h3>
                    <p>Omzet Penjualan Tercatat</p>
                </div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($summary['royalty'], 0, ',', '.') }}</h3>
                    <p>Estimasi Royalti Anda</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Daftar Buku Royalti Kurasi Admin</strong>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Judul Buku</th>
                        <th>Distribusi</th>
                        <th class="text-right">Penjualan</th>
                        <th class="text-right">Harga Acuan</th>
                        <th class="text-right">Omzet</th>
                        <th class="text-right">Rate Royalti</th>
                        <th class="text-right">Royalti</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $book = $row['book'];
                            $channels = [];
                            if ($book->royalty_distribution_online) {
                                $channels[] = 'Online';
                            }
                            if ($book->royalty_distribution_ebook) {
                                $channels[] = 'Ebook';
                            }
                            if ($book->royalty_distribution_marketplace) {
                                $channels[] = 'Marketplace';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $book->judul }}</div>
                                <small class="text-muted">{{ $book->nomor_naskah }}</small>
                            </td>
                            <td>{{ !empty($channels) ? implode(', ', $channels) : '-' }}</td>
                            <td class="text-right">{{ number_format($row['sales_qty']) }}</td>
                            <td class="text-right">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($row['gross'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['rate'] * 100, 2) }}%</td>
                            <td class="text-right font-weight-bold text-success">Rp
                                {{ number_format($row['royalty'], 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex" style="gap:.35rem;">
                                    @if ($book->royalty_agreement_file_path)
                                        <a href="{{ route('author.royalties.document', ['book' => $book, 'type' => 'agreement']) }}"
                                            class="btn btn-xs btn-outline-primary">
                                            Surat Perjanjian
                                        </a>
                                    @endif
                                    @if ($book->royalty_contract_file_path)
                                        <a href="{{ route('author.royalties.document', ['book' => $book, 'type' => 'contract']) }}"
                                            class="btn btn-xs btn-outline-success">
                                            Kontrak
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada buku Anda yang diaktifkan admin untuk distribusi dan program royalti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><strong>Ledger Royalti per Periode</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Periode</th>
                        <th>Buku</th>
                        <th class="text-right">Omzet</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Royalti</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ledgers as $ledger)
                        <tr>
                            <td>{{ $ledger->period_start->format('M Y') }}</td>
                            <td>{{ $ledger->book->judul ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($ledger->gross_amount, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($ledger->royalty_rate * 100, 2) }}%</td>
                            <td class="text-right font-weight-bold text-success">Rp
                                {{ number_format($ledger->royalty_amount, 0, ',', '.') }}</td>
                            <td>
                                <span
                                    class="badge badge-{{ $ledger->status === 'accrued' ? 'warning' : ($ledger->status === 'requested' ? 'info' : 'success') }}">{{ strtoupper($ledger->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Ledger royalti belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><strong>Riwayat Pencairan Royalti</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Rekening</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payoutHistory as $request)
                        <tr>
                            <td>{{ $request->requested_at?->format('d M Y H:i') }}</td>
                            <td>Rp {{ number_format($request->amount, 0, ',', '.') }}</td>
                            <td>
                                {{ $request->bank_name }}<br>
                                <small class="text-muted">{{ $request->bank_account_holder }} -
                                    {{ $request->bank_account_number }}</small>
                            </td>
                            <td><span
                                    class="badge badge-{{ $request->status === 'pending' ? 'warning' : 'success' }}">{{ strtoupper($request->status) }}</span>
                            </td>
                            <td>{{ $request->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada permintaan pencairan royalti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
