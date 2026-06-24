@extends('adminlte::page')

@section('title', 'Layout Generator')

@section('content_header')
    <h1 class="m-0 text-dark">Layout Generator</h1>
@stop

@section('content')
    <div class="card card-primary card-outline shadow">
        <div class="card-header border-0">
            <h3 class="card-title">
                <i class="fas fa-layer-group mr-1"></i> Daftar Naskah
            </h3>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="15%" class="text-center align-middle">No Naskah</th>
                            <th class="align-middle">Judul</th>
                            <th class="align-middle">Penulis</th>
                            <th width="10%" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr>
                                <td class="text-center align-middle">
                                    <span class="badge badge-secondary">{{ $book->nomor_naskah }}</span>
                                </td>
                                <td class="align-middle font-weight-bold">
                                    {{ $book->judul }}
                                </td>
                                <td class="align-middle">
                                    {{ $book->penulis_1 }}
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('layout-generator.show', $book) }}"
                                        class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block text-gray"></i>
                                    <h5>Belum ada data naskah</h5>
                                    <p>Data naskah yang ditambahkan akan muncul di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tampilkan footer hanya jika ada pagination --}}
        @if ($books->hasPages())
            <div class="card-footer bg-white clearfix">
                <div class="float-right m-0">
                    {{ $books->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
