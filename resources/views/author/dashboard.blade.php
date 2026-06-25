@extends('adminlte::page')

@section('title', 'Portal Penulis')

@section('css')
    <style>
        .hero-card {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 60%, #60a5fa 100%);
            border: 0;
            border-radius: 16px;
            color: #fff;
        }

        .stat-box {
            border: 0;
            border-radius: 14px;
            transition: transform .15s;
        }

        .stat-box:hover {
            transform: translateY(-2px);
        }

        .section-heading {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: .9rem;
        }

        .section-heading i {
            color: #3b82f6;
        }

        .workflow-step {
            display: inline-flex;
            align-items: center;
            padding: .28rem .62rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            background: #f3f4f6;
            color: #6b7280;
            white-space: nowrap;
        }

        .workflow-step.done {
            background: #dcfce7;
            color: #166534;
        }

        .workflow-step.active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .inv-row td {
            vertical-align: middle;
        }

        .pkg-feature {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            margin: .15rem;
        }

        .pkg-feature.missing {
            background: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
            text-decoration: line-through;
        }

        .checklist-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .55rem .75rem;
            border-radius: 8px;
            margin-bottom: .35rem;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .checklist-item.done {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .revision-badge {
            font-size: .72rem;
            padding: .2rem .5rem;
        }
    </style>
@endsection

@section('content_header')
    <h1 class="m-0">Portal Penulis</h1>
@endsection

@section('content')
    <div class="d-flex flex-column" style="gap:1.1rem;">

        @foreach (['success', 'warning', 'danger', 'info'] as $type)
            @if (session($type))
                <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {!! session($type) !!}
                </div>
            @endif
        @endforeach

        {{-- HERO --}}
        <div class="card hero-card shadow">
            <div class="card-body py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                    <div>
                        <h2 class="mb-1 font-weight-bold">Selamat datang, {{ auth()->user()->name }}</h2>
                        <p class="mb-0 opacity-75">Pantau produksi buku, invoice, royalti, dan review dari satu tempat.</p>
                    </div>
                    <div class="d-flex align-items-center" style="gap:.45rem;">
                        <a href="{{ route('author.orders.index') }}"
                            class="btn btn-success btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-shopping-cart mr-1"></i> Order Paket/Cetak
                        </a>
                        <a href="{{ route('author.claims.index') }}"
                            class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-id-card mr-1"></i> Claim Buku
                        </a>
                        <a href="{{ route('author.invoices.index') }}"
                            class="btn btn-light btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-file-invoice-dollar mr-1 text-primary"></i> Lihat Semua Invoice
                            @if (($invoiceStats['count_pending'] ?? 0) > 0)
                                <span class="badge badge-danger ml-1">{{ $invoiceStats['count_pending'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('author.royalties.index') }}"
                            class="btn btn-outline-light btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-coins mr-1"></i> Royalti & Kontrak
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-radius:14px;">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Pencairan Royalti</div>
                    <div class="h4 mb-1 font-weight-bold">Rp {{ number_format($royaltyAvailablePayout ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="small {{ $royaltyBankComplete ? 'text-success' : 'text-warning' }}">
                        {{ $royaltyBankComplete ? 'Rekening sudah lengkap' : 'Lengkapi rekening untuk pencairan' }}
                    </div>
                </div>
                <div class="d-flex" style="gap:.5rem;">
                    <a href="{{ route('author.royalties.index') }}" class="btn btn-primary">
                        <i class="fas fa-coins mr-1"></i> Buka Royalti
                    </a>
                    <a href="{{ route('author.royalties.index') }}#payout-request" class="btn btn-outline-secondary">
                        Ajukan Pencairan
                    </a>
                </div>
            </div>
        </div>

        {{-- STATISTIK GLOBAL --}}
        <div class="row" style="row-gap:.75rem;">
            @php
                $globalStats = [
                    [
                        'label' => 'Total Naskah',
                        'value' => $stats['total'],
                        'icon' => 'fa-book',
                        'color' => 'bg-primary',
                    ],
                    [
                        'label' => 'Sedang Produksi',
                        'value' => $stats['in_production'],
                        'icon' => 'fa-cogs',
                        'color' => 'bg-info',
                    ],
                    [
                        'label' => 'Perlu Review',
                        'value' => $stats['awaiting_review'],
                        'icon' => 'fa-eye',
                        'color' => 'bg-warning',
                    ],
                    [
                        'label' => 'Selesai / ISBN',
                        'value' => $stats['completed'],
                        'icon' => 'fa-check-circle',
                        'color' => 'bg-success',
                    ],
                ];
            @endphp
            @foreach ($globalStats as $s)
                <div class="col-6 col-md-3">
                    <div class="small-box stat-box {{ $s['color'] }} shadow-sm">
                        <div class="inner">
                            <h3>{{ $s['value'] }}</h3>
                            <p>{{ $s['label'] }}</p>
                        </div>
                        <div class="icon"><i class="fas {{ $s['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- RINGKASAN INVOICE --}}
        @if ($invoices->isNotEmpty())
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="section-heading">
                        <i class="fas fa-file-invoice-dollar"></i> Invoice &amp; Tagihan
                        <span class="ml-auto">
                            <a href="{{ route('author.invoices.index') }}" class="btn btn-outline-primary btn-sm">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <div class="p-3 rounded border" style="background:#fefce8;">
                                <div class="small text-muted mb-1">Tagihan Belum Lunas</div>
                                <div class="font-weight-bold text-warning" style="font-size:1.1rem;">
                                    Rp {{ number_format($invoiceStats['total_pending'], 0, ',', '.') }}
                                </div>
                                <div class="small text-muted">{{ $invoiceStats['count_pending'] }} invoice pending</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded border" style="background:#f0fdf4;">
                                <div class="small text-muted mb-1">Sudah Dibayar</div>
                                <div class="font-weight-bold text-success" style="font-size:1.1rem;">
                                    Rp {{ number_format($invoiceStats['total_paid'], 0, ',', '.') }}
                                </div>
                                <div class="small text-muted">{{ $invoiceStats['count_paid'] }} invoice lunas</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded border bg-light">
                                <div class="small text-muted mb-1">Total Kewajiban</div>
                                <div class="font-weight-bold" style="font-size:1.1rem;">
                                    Rp
                                    {{ number_format($invoiceStats['total_pending'] + $invoiceStats['total_paid'], 0, ',', '.') }}
                                </div>
                                <div class="small text-muted">semua invoice</div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Naskah</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices->take(5) as $inv)
                                    <tr class="inv-row">
                                        <td><code>{{ $inv->invoice_number }}</code></td>
                                        <td>{{ Str::limit($inv->book->judul ?? '-', 30) }}</td>
                                        <td>{{ $inv->getTypeLabel() }}</td>
                                        <td>Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($inv->due_date)
                                                <span
                                                    class="{{ $inv->isPending() && $inv->due_date->isPast() ? 'text-danger font-weight-bold' : '' }}">
                                                    {{ $inv->due_date->format('d M Y') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><span
                                                class="badge badge-{{ $inv->getStatusBadgeColor() }}">{{ $inv->getStatusLabel() }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('author.invoices.show', $inv) }}"
                                                class="btn btn-xs btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ROYALTI ESTIMASI --}}
        @if ($royaltyData->isNotEmpty())
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="section-heading"><i class="fas fa-coins"></i> Estimasi Royalti</div>
                </div>
                <div class="card-body pt-0">
                    <div class="alert alert-light border mb-3 py-2 small">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        Royalti hanya untuk buku yang diaktifkan admin pada program distribusi.
                        Persentase royalti mengikuti perjanjian buku masing-masing.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Judul</th>
                                    <th class="text-right">Jumlah Cetak</th>
                                    <th class="text-right">Harga Buku</th>
                                    <th class="text-right">Est. Royalti</th>
                                    <th class="text-right">Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($royaltyData as $row)
                                    <tr>
                                        <td>{{ Str::limit($row['judul'], 35) }}</td>
                                        <td class="text-right">{{ number_format($row['print_qty']) }} eks</td>
                                        <td class="text-right">Rp {{ number_format($row['book_price'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            Rp {{ number_format($row['estimated_royalty'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-right">
                                            {{ number_format(($row['royalty_rate'] ?? 0.2) * 100, 2) }}%</td>
                                        <td>
                                            @if (($row['royalty_status'] ?? '') === 'actual')
                                                <span class="badge badge-success">Aktual Penjualan</span>
                                            @else
                                                <span
                                                    class="badge badge-{{ $row['is_complete'] ? 'info' : 'secondary' }}">
                                                    {{ $row['is_complete'] ? 'Estimasi Produksi' : 'Estimasi' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- DETAIL TIAP NASKAH --}}
        @if ($books->isEmpty())
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3 d-block"></i>
                    <h5>Belum ada naskah yang ditugaskan</h5>
                    <p class="text-muted">Hubungi penerbit untuk informasi lebih lanjut.</p>
                </div>
            </div>
        @else
            @foreach ($books as $book)
                @php
                    $packageRev = ($revisionCounts[$book->id] ?? collect())->keyBy('stage');
                    $isReview = in_array($book->workflow_status, ['editing_review', 'layout_review', 'cover_review']);
                    $isDone = in_array($book->workflow_status, ['isbn_approved', 'selesai']);
                    $bookInvoices = $invoices->where('book_id', $book->id);
                @endphp
                <div class="card shadow-sm" style="border-radius:14px; overflow:hidden;">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">
                            <div>
                                <h5 class="mb-1 font-weight-bold">{{ $book->judul }}</h5>
                                <div class="text-muted small">
                                    No. Naskah: <strong>{{ $book->nomor_naskah }}</strong>
                                    @if ($book->isbn)
                                        &nbsp;|&nbsp; ISBN: <strong>{{ $book->isbn }}</strong>
                                    @endif
                                    @if ($book->penulis_2)
                                        &nbsp;|&nbsp; Penulis 2: {{ $book->penulis_2 }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap:.35rem;">
                                @php $statusColor = $isReview ? 'warning' : ($isDone ? 'success' : 'primary'); @endphp
                                <span class="badge badge-{{ $statusColor }} px-3 py-2">
                                    {{ strtoupper(str_replace('_', ' ', $book->workflow_status)) }}
                                </span>
                                <span class="badge badge-light border px-3 py-2">{{ $book->progressPercent() }}%</span>
                                @if ($isReview)
                                    <span class="badge badge-danger px-2 py-2"><i class="fas fa-bell"></i> Aksi
                                        Diperlukan</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body" style="background:#fafbfc;">
                        <div class="mb-3">
                            <div class="progress" style="height:8px; border-radius:4px;">
                                <div class="progress-bar bg-primary {{ $isReview ? 'progress-bar-animated progress-bar-striped' : '' }}"
                                    style="width:{{ $book->progressPercent() }}%;"></div>
                            </div>
                        </div>

                        @php $tabId = 'book-' . $book->id; @endphp
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-overview-{{ $tabId }}">
                                    <i class="fas fa-info-circle mr-1"></i> Ringkasan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $isReview ? 'text-warning font-weight-bold' : '' }}"
                                    data-toggle="tab" href="#tab-review-{{ $tabId }}">
                                    <i class="fas fa-file-signature mr-1"></i> Review
                                    @if ($isReview)
                                        <span class="badge badge-danger ml-1">!</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-paket-{{ $tabId }}">
                                    <i class="fas fa-box-open mr-1"></i> Paket &amp; Checklist
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-invoice-{{ $tabId }}">
                                    <i class="fas fa-receipt mr-1"></i> Invoice
                                    @if ($bookInvoices->where('status', 'pending')->count() > 0)
                                        <span
                                            class="badge badge-warning ml-1">{{ $bookInvoices->where('status', 'pending')->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-revisi-{{ $tabId }}">
                                    <i class="fas fa-undo mr-1"></i> Riwayat Revisi
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- TAB: RINGKASAN --}}
                            <div class="tab-pane fade show active" id="tab-overview-{{ $tabId }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 rounded border bg-white h-100">
                                            <div class="section-heading"><i class="fas fa-users"></i> Tim Produksi</div>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tbody>
                                                    @foreach (['editor' => 'Editor', 'layouter' => 'Layouter', 'designer' => 'Desain Sampul'] as $role => $label)
                                                        <tr>
                                                            <td class="text-muted pl-0" width="130">
                                                                {{ $label }}</td>
                                                            <td>{{ optional($book->assignments->where('role', $role)->first())->person_name ?? '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td class="text-muted pl-0">ISBN</td>
                                                        <td>{{ $book->isbn ?? 'Belum terbit' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted pl-0">Jumlah Cetak</td>
                                                        <td>{{ $book->jumlah_cetak ? number_format($book->jumlah_cetak) . ' eks' : '-' }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 rounded border bg-white h-100">
                                            <div class="section-heading"><i class="fas fa-route"></i> Alur Produksi</div>
                                            <div class="d-flex flex-wrap align-items-center" style="gap:.25rem;">
                                                @foreach (\App\Models\Book::WORKFLOWS as $idx => $step)
                                                    @php $stepIdx = $book->workflowIndex(); @endphp
                                                    <span
                                                        class="workflow-step {{ $idx < $stepIdx ? 'done' : ($idx === $stepIdx ? 'active' : '') }}">
                                                        @if ($idx < $stepIdx)
                                                            <i class="fas fa-check mr-1" style="font-size:.65rem;"></i>
                                                        @endif
                                                        {{ strtoupper(str_replace('_', ' ', $step)) }}
                                                    </span>
                                                    @if (!$loop->last)
                                                        <i class="fas fa-chevron-right text-muted"
                                                            style="font-size:.6rem;"></i>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="mt-3">
                                                @php
                                                    $milestones = [
                                                        'Mulai Editing' => $book->tanggal_mulai_editing,
                                                        'Mulai Layout' => $book->tanggal_mulai_layout,
                                                        'Mulai Cover' => $book->tanggal_mulai_cover,
                                                        'ACC Penulis' => $book->tanggal_acc_penulis,
                                                        'ISBN Terbit' => $book->tanggal_isbn_terbit,
                                                    ];
                                                @endphp
                                                @foreach ($milestones as $label => $date)
                                                    @if ($date)
                                                        <div class="d-flex justify-content-between small mb-1">
                                                            <span class="text-muted">{{ $label }}</span>
                                                            <span>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: REVIEW --}}
                            <div class="tab-pane fade" id="tab-review-{{ $tabId }}">
                                <div class="row">
                                    <div class="col-md-7 mb-3">
                                        <div class="p-3 rounded border bg-white">
                                            <div class="section-heading"><i class="fas fa-file-signature"></i> Aksi Review
                                            </div>
                                            @php
                                                $reviewStage = null;
                                                $reviewLabel = '';
                                                if ($book->workflow_status === 'editing_review') {
                                                    $reviewStage = 'editing';
                                                    $reviewLabel = 'Editing';
                                                } elseif ($book->workflow_status === 'layout_review') {
                                                    $reviewStage = 'layout';
                                                    $reviewLabel = 'Layout';
                                                } elseif ($book->workflow_status === 'cover_review') {
                                                    $reviewStage = 'cover';
                                                    $reviewLabel = 'Desain Sampul';
                                                }
                                            @endphp
                                            @if ($reviewStage)
                                                @php
                                                    $prevRevCount =
                                                        optional($packageRev->get($reviewStage))->total ?? 0;
                                                    $estRevFee = optional($book->publishingPackage)->price
                                                        ? round($book->publishingPackage->price * 0.15, 0)
                                                        : 0;
                                                @endphp
                                                <div class="alert alert-info py-2 mb-3">
                                                    <i class="fas fa-bell mr-1"></i>
                                                    Tahap <strong>{{ $reviewLabel }}</strong> siap direview.
                                                    <br>
                                                    <small>
                                                        @if ($prevRevCount < 1)
                                                            Revisi pertama tahap ini <strong>gratis</strong>.
                                                        @else
                                                            Revisi selanjutnya berbayar
                                                            @if ($estRevFee > 0)
                                                                (<strong>Rp
                                                                    {{ number_format($estRevFee, 0, ',', '.') }}</strong>)
                                                                .
                                                            @else
                                                                .
                                                            @endif
                                                        @endif
                                                    </small>
                                                </div>
                                                @php
                                                    $rfTypeMap = [
                                                        'editing' => 'edited_manuscript',
                                                        'layout' => 'layout_pdf',
                                                        'cover' => 'cover_final',
                                                    ];
                                                    $reviewFiles = $book->activeFiles->where(
                                                        'type',
                                                        $rfTypeMap[$reviewStage] ?? '',
                                                    );
                                                @endphp
                                                @if ($reviewFiles->isNotEmpty())
                                                    <div class="mb-3">
                                                        <strong class="small">Dokumen untuk direview:</strong>
                                                        @foreach ($reviewFiles as $rf)
                                                            <div
                                                                class="d-flex justify-content-between align-items-center border rounded p-2 mt-1 bg-light">
                                                                <div class="small">
                                                                    <strong>{{ strtoupper($rf->type) }}</strong>
                                                                    v{{ $rf->version ?? 1 }}
                                                                    <span
                                                                        class="text-muted ml-1">{{ $rf->original_name ?? '' }}</span>
                                                                </div>
                                                                <a href="{{ route('files.download', $rf) }}"
                                                                    class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-download mr-1"></i> Unduh
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <form method="POST" action="{{ route('author.review.approve', $book) }}"
                                                    class="mb-3">
                                                    @csrf
                                                    <input type="hidden" name="stage" value="{{ $reviewStage }}">
                                                    <button class="btn btn-success btn-sm">
                                                        <i class="fas fa-check mr-1"></i> ACC {{ $reviewLabel }}
                                                    </button>
                                                </form>
                                                <form method="POST" enctype="multipart/form-data"
                                                    action="{{ route('author.review.revision', $book) }}">
                                                    @csrf
                                                    <input type="hidden" name="stage" value="{{ $reviewStage }}">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Catatan Revisi <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="note" class="form-control form-control-sm" rows="3"
                                                            placeholder="Jelaskan apa yang perlu direvisi..." required></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Lampiran (opsional)</label>
                                                        <input type="file" name="attachment"
                                                            class="form-control-file form-control-sm">
                                                    </div>
                                                    <button class="btn btn-danger btn-sm">
                                                        <i class="fas fa-undo mr-1"></i> Minta Revisi
                                                        <span class="badge badge-light ml-1">
                                                            {{ $prevRevCount < 1 ? 'Gratis' : 'Berbayar' }}
                                                        </span>
                                                    </button>
                                                </form>
                                            @else
                                                <div class="alert alert-light border mb-0">
                                                    <i class="fas fa-clock mr-1 text-muted"></i>
                                                    Tidak ada aksi review saat ini. Tahap aktif:
                                                    <strong>{{ strtoupper(str_replace('_', ' ', $book->workflow_status)) }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <div class="p-3 rounded border bg-white h-100">
                                            <div class="section-heading"><i class="fas fa-history"></i> Riwayat Review
                                            </div>
                                            @forelse ($book->reviews()->latest()->limit(5)->get() as $rev)
                                                <div class="border rounded p-2 mb-2 small">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <strong>{{ strtoupper($rev->stage) }}</strong>
                                                        <span
                                                            class="badge badge-{{ $rev->status === 'approved' ? 'success' : 'danger' }}">
                                                            {{ $rev->status === 'approved' ? 'ACC' : 'REVISI' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-muted" style="font-size:.72rem;">
                                                        {{ $rev->created_at->format('d M Y H:i') }}</div>
                                                    @if ($rev->note)
                                                        <div class="mt-1">{{ Str::limit($rev->note, 80) }}</div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-muted small">Belum ada riwayat review.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB: PAKET & CHECKLIST --}}
                            <div class="tab-pane fade" id="tab-paket-{{ $tabId }}">
                                @if ($book->publishingPackage)
                                    @php $pkg = $book->publishingPackage; @endphp
                                    <div class="row">
                                        <div class="col-md-5 mb-3">
                                            <div class="p-3 rounded border bg-white h-100">
                                                <div class="section-heading"><i class="fas fa-box-open"></i>
                                                    {{ $pkg->name }}</div>
                                                @if ($pkg->description)
                                                    <p class="text-muted small">{{ $pkg->description }}</p>
                                                @endif
                                                <div class="mb-2">
                                                    <strong class="small">Harga Paket:</strong>
                                                    <span class="ml-2 text-primary font-weight-bold">
                                                        Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong class="small d-block mb-1">Layanan Termasuk:</strong>
                                                    @foreach (['includes_editing' => 'Editing', 'includes_layout' => 'Layout', 'includes_cover_design' => 'Desain Sampul', 'includes_author_certificate' => 'Sertifikat Penulis', 'includes_google_scholar' => 'Google Scholar', 'requires_hki_registration' => 'Pendaftaran HKI'] as $field => $label)
                                                        <span class="pkg-feature {{ $pkg->$field ? '' : 'missing' }}">
                                                            <i class="fas fa-{{ $pkg->$field ? 'check' : 'times' }}"></i>
                                                            {{ $label }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                                @if ($pkg->default_print_quantity)
                                                    <div class="mt-2 small text-muted">
                                                        Cetak default:
                                                        <strong>{{ number_format($pkg->default_print_quantity) }}
                                                            eks</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <div class="p-3 rounded border bg-white h-100">
                                                <div class="section-heading"><i class="fas fa-tasks"></i> Checklist
                                                    Pengerjaan</div>
                                                @forelse ($book->packageItems()->get() as $item)
                                                    <div class="checklist-item {{ $item->is_completed ? 'done' : '' }}">
                                                        <div>
                                                            <span
                                                                class="font-weight-bold {{ $item->is_completed ? 'text-success' : '' }}">{{ $item->name }}</span>
                                                            @if ($item->is_required)
                                                                <span
                                                                    class="badge badge-danger revision-badge ml-1">Wajib</span>
                                                            @endif
                                                            @if ($item->assigned_to_role)
                                                                <span
                                                                    class="badge badge-light border revision-badge ml-1">{{ ucfirst($item->assigned_to_role) }}</span>
                                                            @endif
                                                        </div>
                                                        <i
                                                            class="fas fa-{{ $item->is_completed ? 'check-circle text-success' : 'clock text-muted' }}"></i>
                                                    </div>
                                                @empty
                                                    <p class="text-muted small">Checklist belum tersedia. Hubungi admin.
                                                    </p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-light border">
                                        <i class="fas fa-info-circle mr-1 text-info"></i>
                                        Paket penerbitan belum dipilih untuk naskah ini. Hubungi penerbit.
                                    </div>
                                @endif
                            </div>

                            {{-- TAB: INVOICE PER NASKAH --}}
                            <div class="tab-pane fade" id="tab-invoice-{{ $tabId }}">
                                @php
                                    $canAccessDeliveryLinks = $book->canAuthorAccessDeliveryLinks();
                                    $hasAnyDeliveryLink =
                                        !empty($book->final_drive_link) || !empty($book->final_ebook_link);
                                @endphp

                                <div class="alert alert-{{ $canAccessDeliveryLinks ? 'success' : 'warning' }} py-2">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap"
                                        style="gap:.5rem;">
                                        <div>
                                            <i class="fas fa-lock-open mr-1"></i>
                                            <strong>Akses File Hasil Produksi</strong>
                                            <div class="small mt-1">
                                                @if ($canAccessDeliveryLinks)
                                                    Pelunasan paket telah terverifikasi. Link Drive/Ebook dapat diakses.
                                                @else
                                                    Link Drive/Ebook akan aktif setelah pelunasan paket lunas atau dibuka
                                                    manual oleh admin finance.
                                                @endif
                                            </div>
                                        </div>
                                        <span
                                            class="badge badge-{{ $canAccessDeliveryLinks ? 'success' : 'secondary' }} px-2 py-1">
                                            {{ $canAccessDeliveryLinks ? 'AKTIF' : 'TERKUNCI' }}
                                        </span>
                                    </div>
                                </div>

                                @if ($hasAnyDeliveryLink)
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-2">
                                            <div class="border rounded p-2 bg-light h-100">
                                                <div class="small text-muted mb-1">Link Drive</div>
                                                @if (!empty($book->final_drive_link) && $canAccessDeliveryLinks)
                                                    <a href="{{ $book->final_drive_link }}" target="_blank"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Link Drive
                                                    </a>
                                                @elseif (!empty($book->final_drive_link))
                                                    <span class="badge badge-secondary">Terkunci</span>
                                                @else
                                                    <span class="text-muted small">Belum tersedia.</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <div class="border rounded p-2 bg-light h-100">
                                                <div class="small text-muted mb-1">Link Ebook</div>
                                                @if (!empty($book->final_ebook_link) && $canAccessDeliveryLinks)
                                                    <a href="{{ $book->final_ebook_link }}" target="_blank"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Link Ebook
                                                    </a>
                                                @elseif (!empty($book->final_ebook_link))
                                                    <span class="badge badge-secondary">Terkunci</span>
                                                @else
                                                    <span class="text-muted small">Belum termasuk paket / belum
                                                        tersedia.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($bookInvoices->isEmpty())
                                    <div class="alert alert-light border">
                                        <i class="fas fa-file-invoice mr-1 text-muted"></i>
                                        Belum ada invoice untuk naskah ini.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>No. Invoice</th>
                                                    <th>Jenis</th>
                                                    <th>Deskripsi</th>
                                                    <th class="text-right">Jumlah</th>
                                                    <th>Jatuh Tempo</th>
                                                    <th>Status</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($bookInvoices as $inv)
                                                    <tr>
                                                        <td><code>{{ $inv->invoice_number }}</code></td>
                                                        <td>{{ $inv->getTypeLabel() }}</td>
                                                        <td>{{ Str::limit($inv->description, 40) }}</td>
                                                        <td class="text-right font-weight-bold">Rp
                                                            {{ number_format($inv->amount, 0, ',', '.') }}</td>
                                                        <td>
                                                            @if ($inv->due_date)
                                                                <span
                                                                    class="{{ $inv->isPending() && $inv->due_date->isPast() ? 'text-danger' : '' }}">
                                                                    {{ $inv->due_date->format('d M Y') }}
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td><span
                                                                class="badge badge-{{ $inv->getStatusBadgeColor() }}">{{ $inv->getStatusLabel() }}</span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('author.invoices.show', $inv) }}"
                                                                class="btn btn-xs btn-outline-secondary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <td colspan="3" class="text-right font-weight-bold">Total</td>
                                                    <td class="text-right font-weight-bold text-primary">Rp
                                                        {{ number_format($bookInvoices->sum('amount'), 0, ',', '.') }}
                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- TAB: RIWAYAT REVISI --}}
                            <div class="tab-pane fade" id="tab-revisi-{{ $tabId }}">
                                @foreach (['editing' => 'Editing', 'layout' => 'Layout', 'cover' => 'Desain Sampul'] as $stageKey => $stageLabel)
                                    @php $stageRevs = $book->reviews()->where('stage',$stageKey)->where('status','revision')->latest()->get(); @endphp
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <strong class="mr-2">{{ $stageLabel }}</strong>
                                            @if ($stageRevs->isEmpty())
                                                <span class="badge badge-light border">Belum ada revisi</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $stageRevs->count() }}
                                                    revisi</span>
                                                @if ($stageRevs->count() === 1)
                                                    <span class="badge badge-success ml-1">Ke-1 gratis</span>
                                                @else
                                                    <span class="badge badge-danger ml-1">{{ $stageRevs->count() - 1 }}
                                                        berbayar</span>
                                                @endif
                                            @endif
                                        </div>
                                        @foreach ($stageRevs as $i => $rev)
                                            <div
                                                class="border rounded p-2 mb-2 small {{ $i === 0 ? 'bg-light' : 'bg-white' }}">
                                                <div class="d-flex justify-content-between">
                                                    <span>Revisi ke-{{ $i + 1 }}
                                                        <span
                                                            class="badge badge-{{ $i === 0 ? 'success' : 'warning' }} revision-badge">
                                                            {{ $i === 0 ? 'Gratis' : 'Berbayar' }}
                                                        </span>
                                                    </span>
                                                    <span
                                                        class="text-muted">{{ $rev->created_at->format('d M Y') }}</span>
                                                </div>
                                                @if ($rev->note)
                                                    <div class="mt-1">{{ $rev->note }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                        </div>{{-- end tab-content --}}
                    </div>{{-- end card-body --}}
                </div>{{-- end card --}}
            @endforeach
        @endif

    </div>{{-- end shell --}}
@endsection
