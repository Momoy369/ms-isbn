@extends('adminlte::page')

@section('title', 'Users')

@section('content')

    <div class="card">

        <div class="card-header">

            <a href="{{ route('users.create') }}"
                class="
                btn
                btn-primary
            ">

                Tambah User

            </a>

        </div>

        <div class="card-body">

            <table class="
                table
                table-bordered
            ">

                <thead>

                    <tr>

                        <th>Nama</th>

                        <th>Email</th>

                        <th>KTP</th>

                        <th>Profil Author</th>

                        <th>Role</th>

                        <th width="150">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($users as $user)
                        <tr>

                            <td>

                                {{ $user->name }}

                            </td>

                            <td>

                                {{ $user->email }}

                            </td>

                            <td>

                                {{ $user->ktp_number ?? '-' }}

                            </td>

                            <td>
                                @if ($user->role === 'author')
                                    @if ($user->is_profile_complete)
                                        <span class="badge badge-success">LENGKAP</span>
                                    @else
                                        <span class="badge badge-danger">BELUM LENGKAP</span>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">N/A</span>
                                @endif
                            </td>

                            <td>

                                {{ strtoupper($user->role) }}

                            </td>

                            <td>

                                <a href="{{ route('users.edit', $user) }}"
                                    class="
                                    btn
                                    btn-warning
                                    btn-sm
                                ">

                                    Edit

                                </a>

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection
