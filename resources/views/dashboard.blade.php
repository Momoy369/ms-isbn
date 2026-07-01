@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Dashboard Produksi</h1>
        <a href="{{ route('assignments.index') }}" class="btn btn-dark btn-sm">
            <i class="fas fa-tasks mr-1"></i> Kelola Assignment
        </a>
    </div>
@endsection

@section('content')
    @if (session('info'))
        <div class="alert alert-info alert-dismissible rounded shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('info') }}
        </div>
    @endif

    <style>
        .dash-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);
            color: #fff;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 16px 34px rgba(2, 6, 23, .25);
        }

        .dash-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .metric {
            border-radius: 12px;
            padding: 14px;
            min-height: 110px;
            color: #0f172a;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .metric h2 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: .2px;
        }

        .metric p {
            margin: 6px 0 0;
            color: #475569;
            font-size: .9rem;
        }

        .metric.metric-alert {
            background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
            border-color: #fda4af;
        }

        .metric.metric-good {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #86efac;
        }

        .metric.metric-flow {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #93c5fd;
        }

        .chip {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }

        .chip-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .chip-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .chip-info {
            background: #e0f2fe;
            color: #075985;
        }

        .chip-success {
            background: #dcfce7;
            color: #14532d;
        }

        .list-min {
            max-height: 380px;
            overflow: auto;
        }
    </style>

    <div class="dash-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h3 class="mb-2">Kontrol Penerbitan & Produksi</h3>
                <div class="mb-1">Monitor jalur dari workflow ISBN sampai cetak selesai dalam satu panel.</div>
                <div class="small text-white-50">Editing rata-rata: <strong>{{ $avgEditing }}</strong> hari | Layout
                    rata-rata:
                    <strong>{{ $avgLayout }}</strong> hari
                </div>
            </div>
            <div class="col-md-3 text-md-right mt-3 mt-md-0">
                <div class="h4 mb-0">{{ $summary['total_books'] }}</div>
                <small>Total naskah aktif</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-flow">
                <h2>{{ $summary['ready_for_isbn'] }}</h2>
                <p>Ready for ISBN</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-flow">
                <h2>{{ $summary['isbn_submitted'] ?? 0 }}</h2>
                <p>ISBN Submitted</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-good">
                <h2>{{ $summary['isbn_approved'] ?? 0 }}</h2>
                <p>ISBN Approved</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-good">
                <h2>{{ $summary['selesai'] ?? 0 }}</h2>
                <p>Produksi Selesai</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric">
                <h2>{{ $summary['active_assignments'] }}</h2>
                <p>Assignment Aktif</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-alert">
                <h2>{{ $summary['overdue_assignments'] }}</h2>
                <p>Assignment Terlambat</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric metric-alert">
                <h2>{{ $summary['revisi'] }}</h2>
                <p>Perlu Revisi</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric">
                <h2>{{ $summary['waiting_approval'] }}</h2>
                <p>Menunggu Persetujuan</p>
            </div>
        </div>
    </div>

    <div class="card dash-card mb-3">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <strong>Insight Naskah</strong>
            <span class="small text-muted">A4/A5 margin 2 cm</span>
        </div>
        <div class="card-body">
            <div class="mb-2">
                <span class="chip chip-info">Tercatat: {{ $manuscriptInsights['tracked_books'] }} naskah</span>
                <span class="chip chip-warning">Belum isi halaman mentah: {{ $manuscriptInsights['unknown_books'] }}</span>
                <span class="chip chip-success">Total halaman mentah A4:
                    {{ number_format($manuscriptInsights['sum_a4_pages'], 0, ',', '.') }}</span>
                <span class="chip chip-info">Rata-rata A4: {{ $manuscriptInsights['avg_a4_pages'] }}</span>
                <span class="chip chip-danger">A4 >125: {{ $manuscriptInsights['over_125_a4'] }}</span>
                <span class="chip chip-danger">A5 cetak >100: {{ $manuscriptInsights['over_100_a5_print'] }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No Naskah</th>
                            <th>Judul</th>
                            <th class="text-center">Hal. Mentah A4</th>
                            <th class="text-center">Hal. A5</th>
                            <th class="text-right">Biaya Extra Paket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($manuscriptInsights['top_books'] as $bookInsight)
                            <tr>
                                <td>{{ $bookInsight->nomor_naskah }}</td>
                                <td>{{ $bookInsight->judul }}</td>
                                <td class="text-center">{{ (int) ($bookInsight->manuscript_a4_pages ?? 0) }}</td>
                                <td class="text-center">{{ (int) ($bookInsight->manuscript_a5_pages ?? 0) }}</td>
                                <td class="text-right">Rp
                                    {{ number_format((float) ($bookInsight->package_extra_fee ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Belum ada data halaman mentah naskah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (
        $alerts['overdue_assignments'] > 0 ||
            $alerts['warning_assignments'] > 0 ||
            $alerts['ready_isbn'] > 0 ||
            $alerts['waiting_author'] > 0)
        <div class="card dash-card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Production Alerts</h5>
                @if ($alerts['overdue_assignments'])
                    <span class="chip chip-danger">{{ $alerts['overdue_assignments'] }} overdue assignment</span>
                @endif
                @if ($alerts['warning_assignments'])
                    <span class="chip chip-warning">{{ $alerts['warning_assignments'] }} deadline &lt; 24 jam</span>
                @endif
                @if ($alerts['ready_isbn'])
                    <span class="chip chip-info">{{ $alerts['ready_isbn'] }} siap ISBN</span>
                @endif
                @if ($alerts['waiting_author'])
                    <span class="chip chip-warning">{{ $alerts['waiting_author'] }} menunggu ACC author &gt; 7 hari</span>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card dash-card h-100">
                <div class="card-header bg-white border-0"><strong>Assignment Terlambat</strong></div>
                <div class="card-body list-min">
                    @forelse ($overdueAssignments as $assignment)
                        <div class="border rounded p-2 mb-2">
                            <div class="font-weight-bold">{{ optional($assignment->book)->judul ?? '-' }}</div>
                            <div class="small text-muted">Role: {{ ucfirst($assignment->role) }} | PIC:
                                {{ $assignment->person_name }}</div>
                            <span class="badge badge-danger mt-1">Terlambat {{ $assignment->lateDays() }} hari</span>
                        </div>
                    @empty
                        <div class="text-success">Tidak ada assignment terlambat.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card dash-card h-100">
                <div class="card-header bg-white border-0"><strong>Top Performer</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">Top Editor</h6>
                            <ul class="list-group list-group-flush">
                                @forelse ($topEditors as $editor)
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span>{{ $editor->person_name }}</span>
                                        <strong>{{ $editor->total }}</strong>
                                    </li>
                                @empty
                                    <li class="list-group-item px-0 text-muted">Belum ada data</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <h6 class="mb-2">Top Layouter</h6>
                            <ul class="list-group list-group-flush">
                                @forelse ($topLayouters as $layouter)
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span>{{ $layouter->person_name }}</span>
                                        <strong>{{ $layouter->total }}</strong>
                                    </li>
                                @empty
                                    <li class="list-group-item px-0 text-muted">Belum ada data</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
