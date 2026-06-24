@extends('adminlte::page')

@section('title', 'Assignment')

@section('content')

    <div class="card">

        <div class="card-header">

            Assignment Produksi

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>Buku</th>

                        <th>Role</th>

                        <th>PIC</th>

                        <th>Deadline</th>

                        <th>Status</th>
                        <th>Keterlambatan</th>
                        <th width="120">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($assignments as $assignment)
                        <tr @if ($assignment->getSlaStatus() === 'overdue') class="table-danger" @endif>

                            <td>

                                {{ $assignment->book->judul }}

                            </td>

                            <td>

                                {{ ucfirst($assignment->role) }}

                            </td>

                            <td>

                                {{ $assignment->person_name }}

                            </td>

                            <td>

                                {{ $assignment->deadline_at }}

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

                            <td>

                                @if ($assignment->getSlaStatus() === 'overdue')
                                    {{ $assignment->lateDays() }}

                                    hari
                                @else
                                    -
                                @endif

                            </td>

                            <td>

                                @if (!$assignment->completed_at)
                                    <form method="POST" action="{{ route('assignments.complete', $assignment) }}">

                                        @csrf

                                        <button
                                            class="
                    btn
                    btn-success
                    btn-sm
                ">

                                            Selesai

                                        </button>

                                    </form>
                                @else
                                    <span class="
                text-success
            ">

                                        ✓

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
