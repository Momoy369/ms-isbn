@extends('adminlte::page')

@section('title', 'Finance Invoice Author')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Finance Invoice Author</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
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
        <div class="col-md-4 mb-2">
            <div class="small-box bg-warning shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['pending'], 0, ',', '.') }}</h3>
                    <p>Total Tagihan Pending</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="small-box bg-success shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['paid'], 0, ',', '.') }}</h3>
                    <p>Total Tagihan Lunas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="small-box bg-primary shadow-sm mb-0">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['pending_package'], 0, ',', '.') }}</h3>
                    <p>Sisa Tagihan Paket</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-file-export mr-1"></i> Export Laporan</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('finance.export.invoices') }}" class="row">
                <div class="col-md-3 mb-2">
                    <input type="date" name="start_date" class="form-control" placeholder="Mulai">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" name="end_date" class="form-control" placeholder="Sampai">
                </div>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-primary btn-block" type="submit">
                        <i class="fas fa-file-csv mr-1"></i> Export Invoice CSV
                    </button>
                </div>
            </form>
            <form method="POST" action="{{ route('finance.reminders.run') }}" class="mt-2"
                onsubmit="return confirm('Jalankan reminder sekarang?')">
                @csrf
                <button class="btn btn-outline-warning" type="submit">
                    <i class="fas fa-bell mr-1"></i> Jalankan Reminder Sekarang
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filter Invoice</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Cari invoice/judul/author...">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select name="type" class="form-control">
                        <option value="">Semua Jenis</option>
                        <option value="package_billing" @selected(request('type') === 'package_billing')>Tagihan Paket</option>
                        <option value="revision" @selected(request('type') === 'revision')>Revisi</option>
                        <option value="additional" @selected(request('type') === 'additional')>Layanan Tambahan</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Daftar Invoice Author</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No. Invoice</th>
                            <th>Author</th>
                            <th>Naskah</th>
                            <th>Jenis</th>
                            <th class="text-right">Jumlah</th>
                            <th>Status</th>
                            <th>Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td><code>{{ $inv->invoice_number }}</code></td>
                                <td>{{ $inv->user->name ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($inv->book->judul ?? '-', 28) }}</td>
                                <td>
                                    {{ $inv->getTypeLabel() }}
                                    @if ($inv->is_package_billing)
                                        <div class="small text-muted">Termin {{ $inv->installment_number ?? '-' }}</div>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($inv->amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $inv->getStatusBadgeColor() }}">{{ $inv->getStatusLabel() }}</span>
                                    @if ($inv->paid_at)
                                        <div class="small text-muted mt-1">{{ $inv->paid_at->format('d M Y H:i') }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $inv->payment_method ?? '-' }}
                                    @if ($inv->payment_reference)
                                        <div class="small text-muted">{{ $inv->payment_reference }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($inv->status !== 'paid')
                                        <form method="POST" action="{{ route('finance.invoices.mark-paid', $inv) }}"
                                            class="mb-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success"
                                                onclick="return confirm('Tandai invoice ini sebagai LUNAS?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('finance.invoices.mark-pending', $inv) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-warning"
                                                onclick="return confirm('Kembalikan status invoice ke pending?')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('invoices.pdf', $inv) }}"
                                        class="btn btn-xs btn-outline-secondary mt-1">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data invoice.</td>
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

    <div class="card shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white border-0">
            <h3 class="card-title mb-0"><i class="fas fa-link mr-1"></i> Link Distribusi & Pelunasan Final</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Naskah</th>
                            <th>Author</th>
                            <th>Status Produksi</th>
                            <th>Tagihan Paket</th>
                            <th>Pengaturan Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($completedBooks as $book)
                            @php
                                $packageInvoices = $book->authorInvoices->where('is_package_billing', true);
                                $hasFinalInvoice = $packageInvoices->contains(
                                    fn($x) => (int) $x->installment_number === 2,
                                );
                                $allPaid =
                                    $packageInvoices->isNotEmpty() &&
                                    $packageInvoices->every(fn($x) => $x->status === 'paid');
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $book->judul }}</div>
                                    <div class="small text-muted">{{ $book->nomor_naskah }}</div>
                                </td>
                                <td>{{ optional($book->author)->name ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $book->workflow_status === 'selesai' ? 'success' : 'secondary' }}">
                                        {{ strtoupper($book->workflow_status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small mb-1">
                                        {{ $packageInvoices->where('status', 'paid')->count() }}/{{ max($packageInvoices->count(), 2) }}
                                        termin lunas
                                    </div>
                                    @if (!$hasFinalInvoice)
                                        <form method="POST"
                                            action="{{ route('finance.books.create-final-invoice', $book) }}">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-primary" type="submit">
                                                <i class="fas fa-file-invoice mr-1"></i> Buat Invoice Pelunasan
                                            </button>
                                        </form>
                                    @endif
                                    @if ($allPaid)
                                        <span class="badge badge-success mt-1">Pelunasan Paket Lunas</span>
                                    @endif
                                </td>
                                <td style="min-width:340px;">
                                    <form method="POST" action="{{ route('finance.books.delivery-links', $book) }}">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <input type="url" name="final_drive_link"
                                                class="form-control form-control-sm"
                                                value="{{ old('final_drive_link', $book->final_drive_link) }}"
                                                placeholder="https://drive.google.com/...">
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="url" name="final_ebook_link"
                                                class="form-control form-control-sm"
                                                value="{{ old('final_ebook_link', $book->final_ebook_link) }}"
                                                placeholder="https://... (opsional ebook)">
                                        </div>
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="checkbox" class="custom-control-input"
                                                id="unlock-{{ $book->id }}" name="links_unlocked_manually"
                                                value="1"
                                                {{ old('links_unlocked_manually', $book->links_unlocked_manually) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="unlock-{{ $book->id }}">Buka
                                                akses manual</label>
                                        </div>
                                        <button type="submit" class="btn btn-xs btn-primary">
                                            <i class="fas fa-save mr-1"></i> Simpan Link & Akses
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada buku selesai untuk diproses
                                    finance.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
