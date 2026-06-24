@extends('adminlte::page')

@section('title', 'Daftar Naskah')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-book mr-2"></i>Daftar Naskah</h1>
        <a href="{{ route('books.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Naskah
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h3 class="card-title font-weight-bold text-muted">Data Seluruh Naskah</h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="15%">No Naskah</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr>
                                <td class="align-middle font-weight-bold">{{ $book->nomor_naskah }}</td>
                                <td class="align-middle">{{ $book->judul }}</td>
                                <td class="align-middle">{{ $book->penulis_1 }}</td>
                                <td class="align-middle text-center">
                                    {{-- Sesuaikan warna badge berdasarkan status --}}
                                    <span class="badge badge-info px-3 py-2">{{ strtoupper($book->status) }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-info"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-warning"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    Belum ada data naskah yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Jika Anda menggunakan pagination di controller, tambahkan ini di bawah table --}}
        @if (method_exists($books, 'links'))
            <div class="card-footer bg-white">
                {{ $books->links() }}
            </div>
        @endif
    </div>
@endsection
