@extends('adminlte::page')

@section('title', 'Assignment')

@section('content')

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-pill">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-pill">
            <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-gradient-info shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $assignments->whereNull('completed_at')->count() }}</h3>
                    <p>Assignment Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-gradient-danger shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $assignments->filter(fn($item) => $item->getSlaStatus() === 'overdue')->count() }}</h3>
                    <p>Lewat SLA</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-gradient-success shadow-sm mb-0">
                <div class="inner">
                    <h3>{{ $assignments->whereNotNull('completed_at')->count() }}</h3>
                    <p>Selesai</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold">Assignment Produksi</h5>
            <span class="badge badge-secondary px-3 py-2">{{ $assignments->count() }} item</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-4">Buku</th>
                            <th>Role</th>
                            <th>PIC</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Keterlambatan</th>
                            <th width="140" class="text-center pr-4">Aksi</th>
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
                                <td>{{ $assignment->person_name }}</td>
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
                                <td class="text-center pr-4">
                                    @if (!$assignment->completed_at)
                                        <form method="POST" action="{{ route('assignments.complete', $assignment) }}">
                                            @csrf
                                            <button class="btn btn-success btn-sm px-3">Selesai</button>
                                        </form>
                                    @else
                                        <span class="text-success font-weight-bold">✓</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada assignment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
