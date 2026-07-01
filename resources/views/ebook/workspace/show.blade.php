@extends('adminlte::page')

@section('title', 'Detail Ebook Publishing')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Detail Ebook Publishing</h1>
        <a href="{{ route('ebook.workspace.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
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
                <div class="card-header"><strong>Informasi Order Ebook</strong></div>
                <div class="card-body">
                    <div><strong>Buku:</strong> {{ $book->judul }}</div>
                    <div><strong>No Naskah:</strong> {{ $book->nomor_naskah }}</div>
                    <div><strong>Status:</strong> {{ strtoupper($order->status) }}</div>
                    <div><strong>Platform:</strong> {{ $order->ebook_platform ?? '-' }}</div>
                    <div><strong>Link Publikasi:</strong>
                        @if ($order->ebook_publication_link)
                            <a href="{{ $order->ebook_publication_link }}" target="_blank" rel="noopener">Buka Link</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>Minta Revisi Ebook</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ebook.workspace.request-revision', $order) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Pesan Revisi</label>
                            <textarea name="message" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Lampiran (opsional)</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        <button class="btn btn-warning" type="submit">Kirim Permintaan Revisi</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><strong>Update Status Ebook Publishing</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ebook.workspace.update-status', $order) }}">
                        @csrf
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                @foreach (['paid', 'ebook_revision_requested', 'ebook_publishing', 'ebook_published', 'cancelled'] as $st)
                                    <option value="{{ $st }}" @selected($order->status === $st)>{{ strtoupper($st) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Platform Ebook</label>
                            <input type="text" name="ebook_platform" class="form-control"
                                value="{{ $order->ebook_platform }}"
                                placeholder="Contoh: Google Play Books / Gramedia Digital / Web MS Publishing">
                        </div>
                        <div class="form-group">
                            <label>Link Publikasi</label>
                            <input type="url" name="ebook_publication_link" class="form-control"
                                value="{{ $order->ebook_publication_link }}" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $order->notes }}</textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Komunikasi Ebook Publishing</strong></div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    @php
                        $thread = $book->messages
                            ->filter(
                                fn($msg) => str_contains((string) $msg->message, '[EBOOK_') ||
                                    in_array(
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
                                    <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank"
                                        rel="noopener">Lihat Lampiran</a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">Belum ada komunikasi ebook publishing.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
