@extends('adminlte::page')

@section('title', 'Layout Generator')

@section('content_header')
    <h1 class="m-0 text-dark">Layout Generator</h1>
@stop

@section('content')
    <div class="card card-outline card-info shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('layout-generator.index') }}" class="row">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Cari Naskah</label>
                    <input type="text" name="q" class="form-control" placeholder="No naskah, judul, atau penulis"
                        value="{{ $search ?? request('q') }}">
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Jenis Buku</label>
                    <select name="book_type" class="form-control">
                        <option value="">Semua Jenis</option>
                        <option value="fiction" {{ ($bookType ?? request('book_type')) === 'fiction' ? 'selected' : '' }}>
                            Fiksi</option>
                        <option value="nonfiction"
                            {{ ($bookType ?? request('book_type')) === 'nonfiction' ? 'selected' : '' }}>
                            Non-Fiksi</option>
                        <option value="poetry" {{ ($bookType ?? request('book_type')) === 'poetry' ? 'selected' : '' }}>
                            Puisi</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Kesiapan Layout</label>
                    <select name="readiness" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="ready" {{ ($readiness ?? request('readiness')) === 'ready' ? 'selected' : '' }}>
                            Siap Generate</option>
                        <option value="not_ready"
                            {{ ($readiness ?? request('readiness')) === 'not_ready' ? 'selected' : '' }}>
                            Perlu Dilengkapi
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-info btn-block mr-2">
                        <i class="fas fa-filter mr-1"></i> Terapkan
                    </button>
                    <a href="{{ route('layout-generator.index') }}" class="btn btn-outline-secondary btn-block">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-3 d-flex flex-wrap">
                <span class="badge badge-light border mr-2 mb-2 px-3 py-2">
                    Ditampilkan: <strong>{{ $summary['listed'] ?? $books->count() }}</strong>
                </span>
                <span class="badge badge-success mr-2 mb-2 px-3 py-2">
                    Siap: <strong>{{ $summary['ready'] ?? 0 }}</strong>
                </span>
                <span class="badge badge-warning mb-2 px-3 py-2">
                    Perlu Perbaikan: <strong>{{ $summary['needs_attention'] ?? 0 }}</strong>
                </span>
            </div>
        </div>
    </div>

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
                            <th width="15%" class="text-center align-middle">Section</th>
                            <th width="15%" class="text-center align-middle">Kesiapan</th>
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
                                    <span
                                        class="badge badge-info">{{ $book->sections_generator_count ?? $book->sectionsGenerator->count() }}</span>
                                </td>
                                <td class="text-center align-middle">
                                    @php
                                        $isReady = (bool) ($book->layout_ready ?? false);
                                    @endphp
                                    <span class="badge badge-{{ $isReady ? 'success' : 'warning' }}">
                                        {{ $isReady ? 'Siap' : 'Belum Siap' }}
                                    </span>
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
                                <td colspan="6" class="text-center text-muted py-5">
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
