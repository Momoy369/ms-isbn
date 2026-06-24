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
                    <h3 class="card-title font-weight-bold"><i class="fas fa-edit mr-2 text-warning"></i>Queue Editing</h3>
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
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian editing</td>
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
                    <h3 class="card-title font-weight-bold"><i class="fas fa-layer-group mr-2 text-primary"></i>Queue Layout
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
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian layout</td>
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
                    <h3 class="card-title font-weight-bold"><i class="fas fa-paint-brush mr-2 text-info"></i>Queue Cover
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
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada antrian sampul</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                                    <td class="text-center"><span class="badge badge-secondary">{{ $item->total }}</span>
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
