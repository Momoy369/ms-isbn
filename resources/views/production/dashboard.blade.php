@extends('adminlte::page')

@section('title', 'Production Monitoring')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-chart-line mr-2"></i>Production Monitoring</h1>
        <a href="{{ route('assignments.history') }}" class="btn btn-info btn-sm shadow-sm">
            <i class="fas fa-history mr-1"></i> Riwayat Penugasan
        </a>
    </div>
@stop

@section('content')

    @php
        $statusBadgeMap = [
            'paid' => 'warning',
            'revision_requested' => 'danger',
            'ebook_revision_requested' => 'danger',
            'printing' => 'primary',
            'processing' => 'info',
            'ebook_publishing' => 'primary',
            'print_completed' => 'success',
            'ebook_completed' => 'success',
            'shipping' => 'dark',
            'shipped' => 'secondary',
        ];

        $statusLabelMap = [
            'paid' => 'Menunggu Proses',
            'revision_requested' => 'Revisi Diminta',
            'ebook_revision_requested' => 'Revisi Ebook',
            'printing' => 'Sedang Dicetak',
            'processing' => 'Sedang Diproses',
            'ebook_publishing' => 'Sedang Dipublikasikan',
            'print_completed' => 'Selesai Cetak',
            'ebook_completed' => 'Selesai Ebook',
            'shipping' => 'Dikirim',
            'shipped' => 'Terkirim',
        ];
    @endphp

    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-header">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-filter mr-2"></i>Filter Operasional
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('production.dashboard') }}" class="row">
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Channel</label>
                    <select name="op_channel" class="form-control form-control-sm">
                        <option value="all" {{ $operationsFilters['channel'] === 'all' ? 'selected' : '' }}>Semua
                        </option>
                        <option value="print" {{ $operationsFilters['channel'] === 'print' ? 'selected' : '' }}>Print
                        </option>
                        <option value="ebook" {{ $operationsFilters['channel'] === 'ebook' ? 'selected' : '' }}>Ebook
                        </option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Status</label>
                    <select name="op_status" class="form-control form-control-sm">
                        @foreach ($statusOptions as $statusValue => $statusText)
                            <option value="{{ $statusValue }}"
                                {{ $operationsFilters['status'] === $statusValue ? 'selected' : '' }}>
                                {{ $statusText }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Adaptasi Cetak</label>
                    <select name="op_adaptation" class="form-control form-control-sm">
                        <option value="all" {{ $operationsFilters['adaptation'] === 'all' ? 'selected' : '' }}>Semua
                        </option>
                        <option value="yes" {{ $operationsFilters['adaptation'] === 'yes' ? 'selected' : '' }}>Ya
                        </option>
                        <option value="no" {{ $operationsFilters['adaptation'] === 'no' ? 'selected' : '' }}>Tidak
                        </option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Cari Buku / Author</label>
                    <input type="text" name="op_keyword" value="{{ $operationsFilters['keyword'] }}"
                        class="form-control form-control-sm" placeholder="Masukkan kata kunci...">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Tanggal Mulai</label>
                    <input type="date" name="op_start_date" value="{{ $operationsFilters['start_date_input'] }}"
                        class="form-control form-control-sm">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Tanggal Akhir</label>
                    <input type="date" name="op_end_date" value="{{ $operationsFilters['end_date_input'] }}"
                        class="form-control form-control-sm">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Umur SLA</label>
                    <select name="op_sla_age" class="form-control form-control-sm">
                        @foreach ($slaAgeOptions as $slaValue => $slaText)
                            <option value="{{ $slaValue }}"
                                {{ $operationsFilters['sla_age'] === $slaValue ? 'selected' : '' }}>
                                {{ $slaText }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <a href="{{ route('production.dashboard.export', request()->query()) }}"
                        class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1">Baris per Queue</label>
                    <select name="op_per_page" class="form-control form-control-sm">
                        @foreach ($perPageOptions as $perPageOption)
                            <option value="{{ $perPageOption }}"
                                {{ (int) $operationsFilters['per_page'] === (int) $perPageOption ? 'selected' : '' }}>
                                {{ $perPageOption }} baris
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end mt-2">
                    <a href="{{ route('production.dashboard') }}" class="btn btn-sm btn-light border mr-2">
                        Reset
                    </a>
                    <button type="submit" class="btn btn-sm btn-secondary">
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3>{{ $operationsSummary['print_queue'] }}</h3>
                    <p>Antrian Print</p>
                </div>
                <div class="icon"><i class="fas fa-print"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $operationsSummary['ebook_queue'] }}</h3>
                    <p>Antrian Ebook</p>
                </div>
                <div class="icon"><i class="fas fa-tablet-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $operationsSummary['revision_queue'] }}</h3>
                    <p>Butuh Revisi</p>
                </div>
                <div class="icon"><i class="fas fa-comment-dots"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ $operationsSummary['adaptation_queue'] }}</h3>
                    <p>Adaptasi Cetak</p>
                </div>
                <div class="icon"><i class="fas fa-ruler-combined"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-warning shadow-sm mb-3">
        <div class="card-header">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-bell mr-2"></i>Alert SLA Operasional
            </h3>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center mb-2">
                <span class="badge badge-warning mr-2">Attention (4-7 hari):
                    {{ $operationsSlaData['summary']['attention'] }}</span>
                <span class="badge badge-danger mr-2">Critical (>7 hari):
                    {{ $operationsSlaData['summary']['critical'] }}</span>
                <span class="badge badge-dark">Total Risiko: {{ $operationsSlaData['summary']['total_risk'] }}</span>
            </div>

            @if (count($operationsSlaData['by_status']) > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-center">Threshold</th>
                                <th class="text-center">Attention</th>
                                <th class="text-center">Critical</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operationsSlaData['by_status'] as $slaRow)
                                <tr>
                                    <td>{{ $slaRow['label'] }}</td>
                                    <td class="text-center text-muted">
                                        A: {{ $slaRow['attention_days'] }}h / C: {{ $slaRow['critical_days'] }}h
                                    </td>
                                    <td class="text-center"><span
                                            class="badge badge-warning">{{ $slaRow['attention'] }}</span></td>
                                    <td class="text-center"><span
                                            class="badge badge-danger">{{ $slaRow['critical'] }}</span></td>
                                    <td class="text-center"><span class="badge badge-dark">{{ $slaRow['total'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">Belum ada antrean operasional yang masuk zona risiko SLA untuk filter saat ini.
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-print mr-2 text-primary"></i>Queue Cetak Ulang
                    </h3>
                    <a href="{{ route('printing.workspace.index') }}" class="btn btn-sm btn-outline-primary">Buka
                        Workspace</a>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped text-nowrap m-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($printQueue as $item)
                                <tr>
                                    <td>{{ $item->title ?? (optional($item->book)->judul ?? '-') }}</td>
                                    <td>{{ optional($item->user)->name ?? '-' }}</td>
                                    @php
                                        $printStatusKey = (string) $item->status;
                                        $printBadge = $statusBadgeMap[$printStatusKey] ?? 'secondary';
                                        $printLabel =
                                            $statusLabelMap[$printStatusKey] ??
                                            ucfirst(str_replace('_', ' ', $printStatusKey));
                                    @endphp
                                    <td><span class="badge badge-{{ $printBadge }}">{{ $printLabel }}</span></td>
                                    <td>
                                        @if (str_contains((string) $item->notes, 'AUTO_PRINT_ADAPTATION_REQUIRED'))
                                            <span class="badge badge-warning">Needs Print Adaptation</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada antrean print</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($printQueue->hasPages())
                    <div class="card-footer py-2">
                        {{ $printQueue->appends(request()->except('print_page'))->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-tablet-alt mr-2 text-success"></i>Queue Ebook Publishing
                    </h3>
                    <a href="{{ route('ebook.workspace.index') }}" class="btn btn-sm btn-outline-success">Buka
                        Workspace</a>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped text-nowrap m-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Platform</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ebookQueue as $item)
                                <tr>
                                    <td>{{ $item->title ?? (optional($item->book)->judul ?? '-') }}</td>
                                    <td>{{ optional($item->user)->name ?? '-' }}</td>
                                    @php
                                        $ebookStatusKey = (string) $item->status;
                                        $ebookBadge = $statusBadgeMap[$ebookStatusKey] ?? 'secondary';
                                        $ebookLabel =
                                            $statusLabelMap[$ebookStatusKey] ??
                                            ucfirst(str_replace('_', ' ', $ebookStatusKey));
                                    @endphp
                                    <td><span class="badge badge-{{ $ebookBadge }}">{{ $ebookLabel }}</span></td>
                                    <td>{{ $item->ebook_platform ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada antrean ebook</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ebookQueue->hasPages())
                    <div class="card-footer py-2">
                        {{ $ebookQueue->appends(request()->except('ebook_page'))->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 mt-3">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-comment-dots mr-2 text-warning"></i>Queue
                        Revisi</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revisionQueue as $item)
                                <tr>
                                    <td>{{ $item->title ?? (optional($item->book)->judul ?? '-') }}</td>
                                    @php
                                        $revisionStatusKey = (string) $item->status;
                                        $revisionBadge = $statusBadgeMap[$revisionStatusKey] ?? 'secondary';
                                        $revisionLabel =
                                            $statusLabelMap[$revisionStatusKey] ??
                                            ucfirst(str_replace('_', ' ', $revisionStatusKey));
                                    @endphp
                                    <td><span class="badge badge-{{ $revisionBadge }}">{{ $revisionLabel }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Tidak ada revisi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($revisionQueue->hasPages())
                    <div class="card-footer py-2">
                        {{ $revisionQueue->appends(request()->except('revision_page'))->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 mt-3">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-ruler-combined mr-2 text-danger"></i>Adaptasi
                        Cetak</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adaptationQueue as $item)
                                <tr>
                                    <td>{{ $item->title ?? (optional($item->book)->judul ?? '-') }}</td>
                                    <td><span class="badge badge-warning">Perlu Adaptasi Cetak</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Tidak ada adaptasi cetak</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($adaptationQueue->hasPages())
                    <div class="card-footer py-2">
                        {{ $adaptationQueue->appends(request()->except('adaptation_page'))->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card card-outline card-light shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-layer-group mr-2"></i>Monitoring Produksi Internal (Legacy)
            </h3>
            <button class="btn btn-tool" type="button" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="card-body" style="display: none;">

            {{-- SUMMARY WIDGETS --}}
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3>{{ $totalBooks }}</h3>
                            <p>Total Buku</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger shadow-sm">
                        <div class="inner">
                            <h3>{{ $overdueAssignments }}</h3>
                            <p>Assignment Terlambat</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3>{{ $warningAssignments }}</h3>
                            <p>Deadline &le; 1 Hari</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary shadow-sm">
                        <div class="inner">
                            <h3>{{ $editingOverdue }}</h3>
                            <p>Editing Terlambat</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-edit"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner">
                            <h3>{{ $layoutOverdue }}</h3>
                            <p>Layout Terlambat</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- QUEUE TABLES --}}
            <div class="row">
                {{-- Queue Editing --}}
                <div class="col-md-12">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-edit mr-2 text-warning"></i>Queue
                                Editing
                            </h3>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover table-striped text-nowrap m-0">
                                <thead>
                                    <tr>
                                        <th>Naskah</th>
                                        <th>Editor</th>
                                        <th>Deadline</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($editingQueue as $item)
                                        <tr>
                                            <td>{{ $item->book->judul }}</td>
                                            <td>{{ $item->person_name }}</td>
                                            <td>{{ optional($item->deadline_at)->format('d M Y') }}</td>
                                            <td class="text-center">
                                                @if ($item->getWarningLevel() == 'overdue')
                                                    <span class="badge badge-danger">Terlambat</span>
                                                @elseif($item->getWarningLevel() == 'warning')
                                                    <span class="badge badge-warning">Deadline < 1 Hari</span>
                                                        @else
                                                            <span class="badge badge-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian
                                                editing</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Queue Layout --}}
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i
                                    class="fas fa-layer-group mr-2 text-primary"></i>Queue
                                Layout
                            </h3>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover table-striped text-nowrap m-0">
                                <thead>
                                    <tr>
                                        <th>Naskah</th>
                                        <th>Layouter</th>
                                        <th>Deadline</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($layoutQueue as $item)
                                        <tr>
                                            <td>{{ $item->book->judul }}</td>
                                            <td>{{ $item->person_name }}</td>
                                            <td>{{ optional($item->deadline_at)->format('d M Y') }}</td>
                                            <td class="text-center">
                                                @if ($item->getWarningLevel() == 'overdue')
                                                    <span class="badge badge-danger">Terlambat</span>
                                                @elseif($item->getWarningLevel() == 'warning')
                                                    <span class="badge badge-warning">Deadline < 1 Hari</span>
                                                        @else
                                                            <span class="badge badge-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian
                                                layout</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Queue Cover Design --}}
                <div class="col-md-12">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-paint-brush mr-2 text-info"></i>Queue
                                Cover
                                Design</h3>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover table-striped text-nowrap m-0">
                                <thead>
                                    <tr>
                                        <th>Naskah</th>
                                        <th>Desainer</th>
                                        <th>Deadline</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($coverQueue as $item)
                                        <tr>
                                            <td>{{ $item->book->judul }}</td>
                                            <td>{{ $item->person_name }}</td>
                                            <td>{{ optional($item->deadline_at)->format('d M Y') }}</td>
                                            <td class="text-center">
                                                @if ($item->getWarningLevel() == 'overdue')
                                                    <span class="badge badge-danger">Terlambat</span>
                                                @elseif($item->getWarningLevel() == 'warning')
                                                    <span class="badge badge-warning">Deadline < 1 Hari</span>
                                                        @else
                                                            <span class="badge badge-success">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian
                                                sampul</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- PRODUCTION PROGRESS --}}
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-tasks mr-2 text-success"></i>Progress Produksi</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover text-nowrap m-0">
                <thead>
                    <tr>
                        <th>Naskah</th>
                        <th>Status</th>
                        <th width="30%">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productionProgress as $book)
                        <tr>
                            <td>{{ $book->judul }}</td>
                            <td><span class="badge badge-light border">{{ $book->workflow_status }}</span></td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="progress progress-sm w-100 mr-2">
                                        <div class="progress-bar bg-success progress-bar-striped" role="progressbar"
                                            style="width: {{ $book->progressPercent() }}%"></div>
                                    </div>
                                    <span
                                        class="text-muted font-weight-bold text-sm">{{ $book->progressPercent() }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- WORKLOADS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2 text-secondary"></i>Beban Editor
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>Editor</th>
                                <th class="text-center">Total Buku</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($editorWorkloads as $item)
                                <tr>
                                    <td>{{ $item->person_name }}</td>
                                    <td class="text-center"><span
                                            class="badge badge-secondary">{{ $item->total }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2 text-secondary"></i>Beban Layouter
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>Layouter</th>
                                <th class="text-center">Total Buku</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($layouterWorkloads as $item)
                                <tr>
                                    <td>{{ $item->person_name }}</td>
                                    <td class="text-center"><span
                                            class="badge badge-secondary">{{ $item->total }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
