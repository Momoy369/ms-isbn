@extends('adminlte::page')

@section('title', 'Ruang File Role')

@section('content_header')
    <h1 class="m-0">Ruang File Role: {{ strtoupper($activeRole) }}</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible rounded">
            <button class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible rounded">
            <button class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    @if ($isSuperadmin)
        <div class="card mb-3">
            <div class="card-header"><strong>Pilih Ruang Role</strong></div>
            <div class="card-body">
                <form method="GET" action="{{ route('role-files.index') }}" class="form-row">
                    <div class="col-md-4 form-group mb-0">
                        <select name="role" class="form-control" onchange="this.form.submit()">
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}" @selected($activeRole === $role)>{{ strtoupper($role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Upload File Role</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('role-files.store') }}" enctype="multipart/form-data" class="row">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="general">General</option>
                        <option value="editorial">Editorial</option>
                        <option value="layout">Layout</option>
                        <option value="design">Design</option>
                        <option value="reference">Reference</option>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Judul File</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-5 form-group">
                    <label>Kaitkan ke Buku (opsional)</label>
                    <select name="book_id" class="form-control">
                        <option value="">- Tidak terkait buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }} ({{ $book->nomor_naskah }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 form-group">
                    <label>Deskripsi (opsional)</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-4 form-group">
                    <label>Pilih File</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Upload</button>
                </div>
            </form>
        </div>
    </div>

    @if (strtolower($activeRole) === 'designer')
        <div class="card mb-3">
            <div class="card-header"><strong>Galeri Desain</strong></div>
            <div class="card-body">
                <div class="row">
                    @forelse ($gallery as $item)
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-2 h-100">
                                <div class="mb-2 text-truncate" title="{{ $item->title }}">
                                    <strong>{{ $item->title }}</strong>
                                </div>
                                <a href="{{ route('role-files.preview', $item) }}" target="_blank">
                                    <img src="{{ route('role-files.preview', $item) }}" alt="{{ $item->title }}"
                                        style="width:100%;height:160px;object-fit:cover;border-radius:6px;">
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">Belum ada desain bergambar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Daftar File Role</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Buku</th>
                        <th>Uploader</th>
                        <th>Ukuran</th>
                        <th>Link</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $item)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $item->title }}</div>
                                @if ($item->description)
                                    <div class="small text-muted">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td>{{ strtoupper($item->category) }}</td>
                            <td>{{ optional($item->book)->judul ?? '-' }}</td>
                            <td>{{ optional($item->uploader)->name ?? '-' }}</td>
                            <td>{{ number_format($item->file_size / 1024, 1) }} KB</td>
                            <td>
                                <input type="text" class="form-control form-control-sm role-file-link"
                                    value="{{ route('role-files.preview', $item) }}" readonly>
                            </td>
                            <td>
                                <a href="{{ route('role-files.preview', $item) }}" class="btn btn-xs btn-outline-info"
                                    target="_blank">Preview</a>
                                <a href="{{ route('role-files.download', $item) }}"
                                    class="btn btn-xs btn-outline-primary">Download</a>
                                <button type="button" class="btn btn-xs btn-outline-secondary copy-link-btn">Copy
                                    Link</button>
                                <form method="POST" action="{{ route('role-files.share', $item) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="expires_days" value="7">
                                    <button type="submit" class="btn btn-xs btn-outline-dark">Share 7 Hari</button>
                                </form>
                                @if (!empty($item->share_token) && !empty($item->share_expires_at) && $item->share_expires_at->isFuture())
                                    <input type="text" class="form-control form-control-sm mt-1 role-file-link"
                                        value="{{ route('role-files.shared', $item->share_token) }}" readonly>
                                @endif
                                <form method="POST" action="{{ route('role-files.destroy', $item) }}" class="d-inline"
                                    onsubmit="return confirm('Hapus file ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada file role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($files->hasPages())
            <div class="card-footer">{{ $files->links() }}</div>
        @endif
    </div>
@endsection

@section('js')
    <script>
        document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const input = btn.closest('tr').querySelector('.role-file-link');
                if (!input) return;

                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value);
                btn.textContent = 'Tersalin';
                setTimeout(function() {
                    btn.textContent = 'Copy Link';
                }, 1200);
            });
        });
    </script>
@endsection
