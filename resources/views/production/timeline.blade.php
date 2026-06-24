@extends('adminlte::page')

@section('title', 'Production Timeline')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-stream mr-2"></i>Production Timeline</h1>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Timeline Produksi Naskah
            </h3>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped text-nowrap m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th>Judul Naskah</th>
                        <th>Editor</th>
                        <th>Layouter</th>
                        <th>Desainer</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Total Hari</th>
                        <th>Tahapan Aktif</th> {{-- Perbaikan: Menambahkan header yang kurang --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td class="text-center align-middle">{{ $book->nomor_naskah }}</td>

                            <td class="align-middle">
                                <a href="{{ route('books.show', $book) }}" class="font-weight-bold">
                                    {{ $book->judul }}
                                </a>
                            </td>

                            <td class="align-middle text-muted">
                                {{ optional($book->assignments->where('role', 'editor')->first())->person_name ?? '-' }}
                            </td>

                            <td class="align-middle text-muted">
                                {{ optional($book->assignments->where('role', 'layouter')->first())->person_name ?? '-' }}
                            </td>

                            <td class="align-middle text-muted">
                                {{ optional($book->assignments->where('role', 'designer')->first())->person_name ?? '-' }}
                            </td>

                            <td class="text-center align-middle">
                                <span class="badge badge-secondary">{{ strtoupper($book->workflow_status) }}</span>
                            </td>

                            <td class="text-center align-middle">
                                <span class="text-bold">{{ floor($book->created_at->diffInDays(now())) }}</span> <span
                                    class="text-muted text-sm">hari</span>
                            </td>

                            <td class="align-middle">
                                @if ($book->tanggal_mulai_editing)
                                    <span class="badge badge-success mr-1"><i class="fas fa-edit mr-1"></i>Editing</span>
                                @endif

                                @if ($book->tanggal_mulai_layout)
                                    <span class="badge badge-info mr-1"><i class="fas fa-layer-group mr-1"></i>Layout</span>
                                @endif

                                @if ($book->tanggal_mulai_cover)
                                    <span class="badge badge-warning mr-1"><i
                                            class="fas fa-paint-brush mr-1"></i>Cover</span>
                                @endif

                                @if ($book->tanggal_pengajuan_isbn)
                                    <span class="badge badge-primary"><i class="fas fa-barcode mr-1"></i>ISBN</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open mb-2 fa-2x"></i><br>
                                Belum ada data timeline produksi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($books->hasPages())
            <div class="card-footer bg-white clearfix">
                <div class="float-right m-0">
                    {{ $books->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
