@extends('adminlte::page')

@section('title', 'File Final Buku')

@section('content_header')
    <h1 class="m-0">File Final Buku: {{ $book->judul }}</h1>
@endsection

@section('content')
    <div class="alert alert-success">
        Akses file final terbuka karena invoice paket sudah lunas 100%.
    </div>

    <div class="card">
        <div class="card-header"><strong>Unduh Berkas Final</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Nama File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklist as $type => $row)
                        <tr>
                            <td>{{ strtoupper(str_replace('_', ' ', $type)) }}</td>
                            <td>{{ $row['file']?->original_name ?? '-' }}</td>
                            <td>
                                @if ($row['exists'])
                                    <a href="{{ route('author.books.final-files.download', [$book, $row['file']]) }}"
                                        class="btn btn-sm btn-success">Download</a>
                                @else
                                    <span class="badge badge-secondary">Belum tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
