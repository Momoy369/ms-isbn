@extends('adminlte::page')

@section('title', 'Detail Order Cetak')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Detail Order Cetak Ulang</h1>
        <a href="{{ route('printing.workspace.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
@endsection

@section('content')
    @foreach (['success', 'warning', 'info', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded">
                <button class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><strong>Informasi Order</strong></div>
                <div class="card-body">
                    <div><strong>Buku:</strong> {{ $book->judul }}</div>
                    <div><strong>No Naskah:</strong> {{ $book->nomor_naskah }}</div>
                    <div><strong>ISBN:</strong> {{ $book->isbn ?? '-' }}</div>
                    <div><strong>Jumlah Cetak:</strong> {{ number_format($order->quantity) }} eksemplar</div>
                    <div><strong>Status Order:</strong> {{ strtoupper($order->status) }}</div>
                    <div><strong>Penulis:</strong> {{ optional($order->user)->name ?? '-' }}</div>
                    <div><strong>Alamat Kirim:</strong> {{ $order->shipping_address ?? '-' }}</div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>Final File Checklist</strong></div>
                <div class="card-body">
                    @foreach ($checklist as $type => $row)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>{{ strtoupper(str_replace('_', ' ', $type)) }}</div>
                            <div>
                                @if ($row['exists'])
                                    <span class="badge badge-success">READY</span>
                                @else
                                    <span class="badge badge-secondary">MISSING</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><strong>Upload Final File Percetakan</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('printing.workspace.upload-final-file', $order) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Tipe File</label>
                            <select name="type" class="form-control" required>
                                <option value="isbn_image">Gambar ISBN</option>
                                <option value="qrcbn_image">Gambar QRCBN</option>
                                <option value="final_layout">Naskah Full Final Layout ACC</option>
                                <option value="final_cover">Sampul Full + ISBN/QRCBN ACC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>File</label>
                            <input type="file" name="file" class="form-control" required>
                            <small class="text-muted">Maks 50MB. Scan metadata akan dijalankan saat upload.</small>
                        </div>
                        <div class="form-group">
                            <label>Catatan Scan</label>
                            <input type="text" name="note" class="form-control"
                                placeholder="Contoh: ISBN + QRCBN sudah sesuai proof ACC author">
                        </div>
                        <button class="btn btn-primary">Upload & Validasi</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>File Final Aktif</strong></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th>File</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($book->files->where('is_active', true)->whereIn('type', array_keys($checklist)) as $file)
                                <tr>
                                    <td>{{ strtoupper(str_replace('_', ' ', $file->type)) }}</td>
                                    <td>{{ $file->original_name }}</td>
                                    <td>{{ $file->note ?: '-' }}</td>
                                    <td>
                                        <a href="{{ route('files.download', $file) }}"
                                            class="btn btn-success btn-sm">Download</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada final file yang aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
