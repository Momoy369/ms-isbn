@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')

    @if (
        $alerts['overdue_assignments'] > 0 ||
            $alerts['warning_assignments'] > 0 ||
            $alerts['ready_isbn'] > 0 ||
            $alerts['waiting_author'] > 0)

        <div class="alert alert-warning">

            <h5>

                Production Alerts

            </h5>

            <ul class="mb-0">

                @if ($alerts['overdue_assignments'])
                    <li>

                        {{ $alerts['overdue_assignments'] }}

                        assignment terlambat

                    </li>
                @endif

                @if ($alerts['warning_assignments'])
                    <li>

                        {{ $alerts['warning_assignments'] }}

                        assignment deadline kurang dari 24 jam

                    </li>
                @endif

                @if ($alerts['ready_isbn'])
                    <li>

                        {{ $alerts['ready_isbn'] }}

                        buku siap diajukan ISBN

                    </li>
                @endif

                @if ($alerts['waiting_author'])
                    <li>

                        {{ $alerts['waiting_author'] }}

                        buku menunggu ACC penulis lebih dari 7 hari

                    </li>
                @endif

            </ul>

        </div>

    @endif

    <div class="row">

        <div class="col-md-3">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>

                        {{ $summary['total_books'] }}

                    </h3>

                    <p>

                        Total Buku

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>

                        {{ $summary['ready_for_isbn'] }}

                    </h3>

                    <p>

                        Ready ISBN

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-danger">

                <div class="inner">

                    <h3>

                        {{ $summary['revisi'] }}

                    </h3>

                    <p>

                        Perlu Revisi

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>

                        {{ $summary['overdue_assignments'] }}

                    </h3>

                    <p>

                        Terlambat

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-warning">

                <div class="inner">

                    <h3>

                        {{ $summary['waiting_approval'] }}

                    </h3>

                    <p>

                        Menunggu Persetujuan

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>

                        {{ $summary['isbn_submitted'] ?? 0 }}

                    </h3>

                    <p>

                        ISBN Submitted

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>

                        {{ $summary['isbn_approved'] ?? 0 }}

                    </h3>

                    <p>

                        ISBN Approved

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-secondary">

                <div class="inner">

                    <h3>

                        {{ $summary['selesai'] ?? 0 }}

                    </h3>

                    <p>

                        Produksi Selesai

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-info">

                <div class="inner">

                    <h3>

                        {{ $summary['active_assignments'] }}

                    </h3>

                    <p>

                        Assignment Aktif

                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-tasks"></i>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="small-box bg-danger">

                <div class="inner">

                    <h3>

                        {{ $summary['overdue_assignments'] }}

                    </h3>

                    <p>

                        Assignment Terlambat

                    </p>

                </div>

                <div class="icon">

                    <i class="fas fa-exclamation-triangle"></i>

                </div>

            </div>

        </div>



    </div>

    <div class="card">

        <div class="card-header">

            Assignment Terlambat

        </div>

        <div class="card-body">

            @forelse($overdueAssignments
                                as $assignment)
                <div class="mb-3">

                    <strong>

                        {{ $assignment->book->judul }}

                    </strong>

                    <br>

                    Role:

                    {{ ucfirst($assignment->role) }}

                    <br>

                    PIC:

                    {{ $assignment->person_name }}

                    <br>

                    <span class="
                        badge
                        badge-danger
                    ">

                        Terlambat

                        {{ $assignment->lateDays() }}

                        hari

                    </span>

                </div>

                <hr>

            @empty

                <div class="
                    text-success
                ">

                    Tidak ada assignment terlambat

                </div>
            @endforelse

        </div>

    </div>

    <div class="card-header">

        Assignment Terlambat

        <a href="{{ route('assignments.index') }}"
            class="
            btn
            btn-sm
            btn-primary
            float-right
        ">

            Lihat Semua

        </a>

    </div>

    <div class="row">

        <div class="col-md-6">

            <div class="small-box bg-primary">

                <div class="inner">

                    <h3>

                        {{ $avgEditing }}

                        hari

                    </h3>

                    <p>

                        Rata-rata Editing

                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="small-box bg-success">

                <div class="inner">

                    <h3>

                        {{ $avgLayout }}

                        hari

                    </h3>

                    <p>

                        Rata-rata Layout

                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="card">

        <div class="card-header">

            Top Editor

        </div>

        <div class="card-body">

            <table class="table">

                <thead>

                    <tr>

                        <th>Nama</th>

                        <th>Total Buku</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($topEditors as $editor)
                        <tr>

                            <td>
                                {{ $editor->person_name }}
                            </td>

                            <td>
                                {{ $editor->total }}
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

    <div class="card">

        <div class="card-header">

            Top Layouter

        </div>

        <div class="card-body">

            <table class="table">

                <thead>

                    <tr>

                        <th>Nama</th>

                        <th>Total Buku</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($topLayouters as $layouter)
                        <tr>

                            <td>
                                {{ $layouter->person_name }}
                            </td>

                            <td>
                                {{ $layouter->total }}
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection
