@extends('adminlte::page')

@section('title', 'Assignment Saya')

@section('content')

    @if (session('info'))
        <div class="alert alert-info alert-dismissible rounded shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('info') }}
        </div>
    @endif

    <div class="card">

        <div class="card-header">

            Assignment Saya

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3>
                                {{ $activeAssignments }}
                            </h3>

                            <p>
                                Assignment Aktif
                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="small-box bg-danger">

                        <div class="inner">

                            <h3>
                                {{ $overdueAssignments }}
                            </h3>

                            <p>
                                Terlambat
                            </p>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3>
                                {{ $completedThisMonth }}
                            </h3>

                            <p>
                                Selesai Bulan Ini
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>Buku</th>

                        <th>Role</th>

                        <th>Deadline</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($assignments as $assignment)
                        <tr>

                            <td>

                                {{ $assignment->book->judul }}

                            </td>

                            <td>

                                {{ ucfirst($assignment->role) }}

                            </td>

                            <td>

                                {{ optional($assignment->deadline_at) }}

                            </td>

                            <td>

                                @if ($assignment->getSlaStatus() === 'completed')
                                    <span class="badge badge-success">

                                        COMPLETED

                                    </span>
                                @elseif($assignment->getSlaStatus() === 'overdue')
                                    <span class="badge badge-danger">

                                        OVERDUE

                                    </span>
                                @else
                                    <span class="badge badge-primary">

                                        ON TRACK

                                    </span>
                                @endif

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection
