@extends('adminlte::page')

@section('title', 'Antrian ISBN')

@section('content')

    <div class="card">

        <div class="card-header">

            Buku Siap ISBN

        </div>

        <div class="card-body">

            <table class="
                table
                table-bordered
            ">

                <thead>

                    <tr>

                        <th>
                            Nomor
                        </th>

                        <th>
                            Judul
                        </th>

                        <th>
                            Penulis
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($books as $book)
                        <tr>

                            <td>

                                {{ $book->nomor_naskah }}

                            </td>

                            <td>

                                {{ $book->judul }}

                            </td>

                            <td>

                                {{ $book->penulis_1 }}

                            </td>

                            <td>

                                <form method="POST"
                                    action="{{ route('books.generate-all', $book) }}">

                                    @csrf

                                    <button
                                        class="
                                        btn
                                        btn-success
                                        btn-sm
                                    ">

                                        Generate Paket

                                    </button>

                                </form>

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@endsection
