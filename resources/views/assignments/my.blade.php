@extends('adminlte::page')

@section('title', 'Assignment Saya')

@section('content')

    @if (session('info'))
        <div class="alert alert-info alert-dismissible rounded-pill border-0 shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('info') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-gradient-info shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $activeAssignments }}</h3>
                    <p>Assignment Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-gradient-danger shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $overdueAssignments }}</h3>
                    <p>Terlambat</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-gradient-success shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $completedThisMonth }}</h3>
                    <p>Selesai Bulan Ini</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold">Assignment Saya</h5>
            <span class="badge badge-secondary px-3 py-2">{{ $assignments->count() }} item</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-4">Buku</th>
                            <th>Role</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Keterlambatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assignments as $assignment)
                            @php
                                $slaStatus = $assignment->getSlaStatus();
                                $deadlineText = $assignment->deadline_at
                                    ? \Carbon\Carbon::parse($assignment->deadline_at)->format('d M Y H:i')
                                    : '-';
                            @endphp
                            <tr @if ($slaStatus === 'overdue') class="table-danger" @endif>
                                <td class="pl-4 font-weight-600">{{ $assignment->book->judul }}</td>
                                <td><span class="badge badge-light">{{ strtoupper((string) $assignment->role) }}</span></td>
                                <td>{{ $deadlineText }}</td>
                                <td>
                                    @if ($slaStatus === 'completed')
                                        <span class="badge badge-success">COMPLETED</span>
                                    @elseif($slaStatus === 'overdue')
                                        <span class="badge badge-danger">OVERDUE</span>
                                    @else
                                        <span class="badge badge-primary">ON TRACK</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($slaStatus === 'overdue')
                                        <strong>{{ $assignment->lateDays() }}</strong> hari
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada assignment untuk Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
