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
                    <div><strong>No Resi:</strong> {{ $order->tracking_number ?? '-' }}</div>
                    <div><strong>Penulis:</strong> {{ optional($order->user)->name ?? '-' }}</div>
                    <div><strong>Alamat Kirim:</strong> {{ $order->shipping_address ?? '-' }}</div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>Minta Revisi ke Layout/Desain</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('printing.workspace.request-revision', $order) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Pesan Revisi</label>
                            <textarea name="message" class="form-control" rows="3" required
                                placeholder="Jelaskan bagian yang harus direvisi oleh tim layout/desain..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Lampiran (opsional)</label>
                            <input type="file" name="attachment" class="form-control">
                            <small class="text-muted">Maks 10MB.</small>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-comment-dots mr-1"></i> Kirim Permintaan Revisi
                        </button>
                    </form>
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

            <div class="card mb-3">
                <div class="card-header"><strong>Riwayat Status</strong></div>
                <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                    @forelse ($order->statusHistories as $history)
                        <div class="border rounded p-2 mb-2">
                            <div class="small text-muted">
                                {{ optional($history->created_at)->format('d M Y H:i') }}
                                @if ($history->changedBy)
                                    • {{ $history->changedBy->name }}
                                @endif
                                @if ($history->context)
                                    • {{ strtoupper($history->context) }}
                                @endif
                            </div>
                            <div>
                                <strong>{{ strtoupper($history->from_status ?? 'BARU') }}</strong>
                                <i class="fas fa-arrow-right mx-1"></i>
                                <strong>{{ strtoupper($history->to_status) }}</strong>
                            </div>
                            @if ($history->note)
                                <div class="small mt-1">{{ $history->note }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">Belum ada riwayat status.</div>
                    @endforelse
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

            <div class="card mt-3">
                <div class="card-header"><strong>Komunikasi Produksi (Percetakan, Layout, Desain)</strong></div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    @php
                        $thread = $book->messages
                            ->filter(
                                fn($msg) => in_array(
                                    $msg->sender_role,
                                    ['designer', 'layouter', 'admin', 'owner', 'finance', 'superadmin'],
                                    true,
                                ),
                            )
                            ->values();
                    @endphp

                    @forelse ($thread as $msg)
                        <div class="border rounded p-2 mb-2">
                            <div class="small text-muted mb-1">
                                <strong>{{ $msg->sender_name }}</strong> ({{ strtoupper($msg->sender_role) }})
                                • {{ optional($msg->created_at)->format('d M Y H:i') }}
                            </div>
                            <div>{{ $msg->message }}</div>
                            @if ($msg->attachment)
                                <div class="mt-1">
                                    <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" rel="noopener">
                                        Lihat Lampiran
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">Belum ada komunikasi revisi untuk buku ini.</div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <div class="small text-muted mb-2">
                        Tip: gunakan prefix <strong>[LAYOUT]</strong> atau <strong>[DESIGN]</strong> agar konteks revisi
                        lebih jelas.
                    </div>
                    <form method="POST" action="{{ route('books.message.store', $book) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-2">
                            <label>Kirim Pesan Produksi</label>
                            <textarea name="message" class="form-control" rows="2" required
                                placeholder="Tulis pesan untuk tim layout/desain..."></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
