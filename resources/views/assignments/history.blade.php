@extends('adminlte::page')

@section('title', 'Riwayat Penugasan')

@section('content')

    <div class="card">

        <div class="card-header">

            Riwayat Penugasan

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th>Tanggal</th>

                        <th>Naskah</th>

                        <th>Role</th>

                        <th>Aktivitas</th>

                        <th>Dari</th>

                        <th>Ke</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($histories as $history)
                        <tr>

                            <td>

                                {{ $history->created_at }}

                            </td>

                            <td>

                                {{ $history->book->judul }}

                            </td>

                            <td>

                                {{ $history->role }}

                            </td>

                            <td>

                                {{ $history->activity }}

                            </td>

                            <td>

                                {{ $history->old_person }}

                            </td>

                            <td>

                                {{ $history->new_person }}

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

            {{ $histories->links() }}

        </div>

    </div>

@endsection
