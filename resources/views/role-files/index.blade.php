@extends('adminlte::page')

@section('title', 'Ruang File Role')

@section('content_header')
    <h1 class="m-0">Ruang File Role: {{ strtoupper($activeRole) }}</h1>
@endsection

@section('content')
    <style>
        .drive-shell {
            border-radius: 16px;
            background: linear-gradient(160deg, #f8fafc 0%, #eef2ff 100%);
            border: 1px solid #e6eaf2;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 16px;
        }

        .drive-toolbar {
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 12px;
            margin-bottom: 14px;
        }

        .drive-stat {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            padding: 10px 12px;
            min-height: 84px;
        }

        .drive-stat .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .drive-stat .value {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .folder-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dbe3f0;
            color: #334155;
            border-radius: 999px;
            padding: 4px 10px;
            margin: 0 6px 8px 0;
            text-decoration: none;
            background: #fff;
            font-size: 12px;
        }

        .folder-chip.active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
        }

        .drive-upload {
            border-radius: 12px;
            border: 1px solid #dbe3f0;
            background: #fff;
            padding: 12px;
        }

        .drive-table-wrap {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dbe3f0;
            background: #fff;
        }

        .drive-table thead th {
            background: #f8fafc;
            border-top: 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            color: #475569;
            text-transform: uppercase;
        }

        .path-pill {
            display: inline-block;
            font-family: Consolas, Monaco, monospace;
            font-size: 11px;
            background: #eef2ff;
            color: #1e3a8a;
            border-radius: 6px;
            padding: 2px 6px;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .view-toggle .btn.active {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .drive-grid-card {
            border: 1px solid #dbe3f0;
            border-radius: 12px;
            background: #fff;
            padding: 12px;
            height: 100%;
        }

        .drive-grid-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .drive-grid-meta {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .access-badge-private {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .access-badge-role {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }

        .access-badge-all {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .access-badge-public {
            background: #f3e8ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
        }

        .dropzone {
            border: 2px dashed #93c5fd;
            border-radius: 10px;
            background: #eff6ff;
            padding: 18px;
            text-align: center;
            color: #1e40af;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .dropzone.dragover {
            background: #dbeafe;
            border-color: #1d4ed8;
        }
    </style>

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

    <div class="drive-shell">
        <form method="GET" action="{{ route('role-files.index') }}" class="drive-toolbar">
            <div class="form-row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label class="mb-1">Cari file</label>
                    <input type="text" class="form-control" name="q" value="{{ $search }}"
                        placeholder="Cari judul, deskripsi, buku, atau path...">
                </div>
                <div class="col-md-3 form-group mb-2">
                    <label class="mb-1">Folder</label>
                    <select name="folder" class="form-control">
                        <option value="">- Semua folder -</option>
                        @foreach ($folders as $folder)
                            <option value="{{ $folder }}" @selected($selectedFolder === $folder)>{{ $folder }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="mb-1">Filter Role File</label>
                    <select name="role_filter" class="form-control">
                        <option value="">Semua role</option>
                        @foreach ($availableRoles as $role)
                            <option value="{{ $role }}" @selected($roleFilter === $role)>{{ strtoupper($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="mb-1">Role Upload</label>
                    @if ($isSuperadmin)
                        <select name="role" class="form-control">
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}" @selected($activeRole === $role)>{{ strtoupper($role) }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ strtoupper($activeRole) }}" readonly>
                    @endif
                </div>
                <div class="col-md-1 form-group mb-2">
                    <button type="submit" class="btn btn-primary btn-block">Go</button>
                </div>
                <input type="hidden" name="view" value="{{ $viewMode }}">
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="small text-muted">
                Semua file ditampilkan lintas role. Jika akses tidak relevan, file tetap terlihat dengan status private.
            </div>
            <div class="btn-group btn-group-sm view-toggle" role="group" aria-label="View Toggle">
                <a href="{{ route('role-files.index', array_filter(['role' => $activeRole, 'q' => $search, 'folder' => $selectedFolder, 'role_filter' => $roleFilter, 'view' => 'table'])) }}"
                    class="btn btn-outline-primary {{ $viewMode === 'table' ? 'active' : '' }}">Table</a>
                <a href="{{ route('role-files.index', array_filter(['role' => $activeRole, 'q' => $search, 'folder' => $selectedFolder, 'role_filter' => $roleFilter, 'view' => 'grid'])) }}"
                    class="btn btn-outline-primary {{ $viewMode === 'grid' ? 'active' : '' }}">Grid</a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="drive-stat">
                    <div class="label">Total File</div>
                    <div class="value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="drive-stat">
                    <div class="label">Terkait Buku</div>
                    <div class="value">{{ number_format($stats['linked_books']) }}</div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="drive-stat">
                    <div class="label">Gambar</div>
                    <div class="value">{{ number_format($stats['images']) }}</div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="drive-stat">
                    <div class="label">Folder</div>
                    <div class="value">{{ number_format($stats['folders']) }}</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <a class="folder-chip {{ $selectedFolder === '' ? 'active' : '' }}"
                href="{{ route('role-files.index', array_filter(['role' => $activeRole, 'q' => $search, 'role_filter' => $roleFilter, 'view' => $viewMode])) }}">
                Semua Folder
            </a>
            @foreach ($folders as $folder)
                <a class="folder-chip {{ $selectedFolder === $folder ? 'active' : '' }}"
                    href="{{ route('role-files.index', array_filter(['role' => $activeRole, 'q' => $search, 'folder' => $folder, 'role_filter' => $roleFilter, 'view' => $viewMode])) }}">
                    {{ basename($folder) }}
                </a>
            @endforeach
        </div>

        <div class="drive-upload mb-3">
            <h5 class="mb-3">Upload File</h5>
            <form method="POST" action="{{ route('role-files.store') }}" enctype="multipart/form-data" class="row"
                id="uploadForm">
                @csrf
                <input type="hidden" name="role" value="{{ $activeRole }}">

                <div class="col-12 mb-2">
                    <div class="alert alert-light border mb-0">
                        Mode folder buku:
                        <strong>role-files/{{ $activeRole }}/books/{kode-naskah}-{judul}-{penulis}</strong>.
                        Mode umum: <strong>role-files/{{ $activeRole }}/general/{tahun}/{bulan}</strong>.
                    </div>
                </div>

                <div class="col-md-3 form-group">
                    <label>Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="general">General</option>
                        <option value="kelengkapan_naskah">Kelengkapan Naskah</option>
                        <option value="final_package">Paket Final</option>
                        <option value="skk">SKK</option>
                        <option value="hki">HKI</option>
                        <option value="sertifikat_penulis">Sertifikat Penulis</option>
                        <option value="editorial">Editorial</option>
                        <option value="layout">Layout</option>
                        <option value="design">Design</option>
                        <option value="reference">Reference</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Judul (opsional, auto jika kosong)</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Paket Final Batch 1">
                </div>
                <div class="col-md-3 form-group">
                    <label>Kaitkan ke Buku (opsional)</label>
                    <select name="book_id" class="form-control">
                        <option value="">- Tidak terkait buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }} ({{ $book->nomor_naskah }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Akses</label>
                    <select name="access_scope" class="form-control">
                        <option value="private">Private (owner/superadmin)</option>
                        <option value="role" selected>Role Terkait</option>
                        <option value="all_roles">Semua Role (internal)</option>
                        <option value="public">Publik (semua orang)</option>
                    </select>
                </div>
                <div class="col-md-8 form-group">
                    <label>Deskripsi (opsional)</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-4 form-group">
                    <label>Sinkron ke Berkas Buku (opsional)</label>
                    <select name="book_file_type" class="form-control">
                        <option value="">- Auto deteksi / tidak sinkron -</option>
                        <option value="isbn_image">ISBN Image</option>
                        <option value="qrcbn_image">QRCBN Image</option>
                        <option value="final_layout">Final Layout</option>
                        <option value="final_cover">Final Cover</option>
                        <option value="skk">SKK</option>
                        <option value="hki">HKI (opsional)</option>
                        <option value="sertifikat_penulis">Sertifikat Penulis</option>
                        <option value="naskah_final">Naskah Final</option>
                        <option value="cover">Cover</option>
                        <option value="layout_pdf">Layout PDF</option>
                    </select>
                </div>

                <div class="col-12 form-group">
                    <label>Upload Multi File (drag & drop)</label>
                    <div class="dropzone" id="dropzone">
                        Tarik file ke sini, atau klik untuk memilih banyak file sekaligus.
                        <div class="small text-muted mt-1" id="fileCounter">Belum ada file dipilih</div>
                    </div>
                    <input type="file" name="files[]" id="filesInput" class="d-none" multiple>
                </div>

                <div class="col-md-2 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Upload</button>
                </div>
            </form>
        </div>

        @if ($gallery->isNotEmpty())
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Preview Cepat (Gambar)</strong></div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($gallery as $item)
                            <div class="col-md-2 col-6 mb-3">
                                <div class="border rounded p-2 h-100 bg-white">
                                    <div class="small text-truncate mb-1" title="{{ $item->title }}">
                                        {{ $item->title }}</div>
                                    <a href="{{ route('role-files.preview', $item) }}" target="_blank">
                                        <img src="{{ route('role-files.preview', $item) }}" alt="{{ $item->title }}"
                                            style="width:100%;height:110px;object-fit:cover;border-radius:6px;">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @php
            $scopeClass = function ($scope) {
                return match ((string) $scope) {
                    'private' => 'access-badge-private',
                    'all_roles' => 'access-badge-all',
                    'public' => 'access-badge-public',
                    default => 'access-badge-role',
                };
            };
        @endphp

        @if ($viewMode === 'grid')
            <div class="row">
                @forelse ($files as $item)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <div class="drive-grid-card">
                            <div class="drive-grid-title text-truncate" title="{{ $item->title }}">{{ $item->title }}
                            </div>
                            <div class="drive-grid-meta">{{ strtoupper($item->category) }} •
                                {{ number_format($item->file_size / 1024, 1) }} KB</div>
                            <div class="mb-2">
                                <span
                                    class="badge {{ $scopeClass($item->access_scope ?? 'role') }}">{{ $item->access_label ?? strtoupper($item->access_scope ?? 'role') }}</span>
                            </div>
                            <div class="small text-muted mb-2 text-truncate"
                                title="{{ optional($item->book)->judul ?? '-' }}">
                                Buku: {{ optional($item->book)->judul ?? '-' }}
                            </div>
                            <div class="mb-2">
                                <span class="path-pill"
                                    title="{{ dirname($item->file_path) }}">{{ dirname($item->file_path) }}</span>
                            </div>
                            <div class="small text-muted mb-2">Uploader: {{ optional($item->uploader)->name ?? '-' }}
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm role-file-link"
                                    value="{{ route('role-files.preview', $item) }}" readonly>
                            </div>

                            @if ($item->can_access)
                                <div class="btn-group btn-group-sm d-flex mb-2" role="group">
                                    <a href="{{ route('role-files.preview', $item) }}" class="btn btn-outline-info"
                                        target="_blank">Preview</a>
                                    <a href="{{ route('role-files.download', $item) }}"
                                        class="btn btn-outline-primary">Download</a>
                                    <button type="button" class="btn btn-outline-secondary copy-link-btn">Copy</button>
                                </div>
                            @else
                                <div class="alert alert-warning py-1 px-2 small mb-2">
                                    Folder/set file private. Anda belum memiliki akses baca.
                                </div>
                            @endif

                            <div class="btn-group btn-group-sm d-flex mb-2" role="group">
                                <button type="button" class="btn btn-outline-warning rename-file-btn"
                                    data-url="{{ route('role-files.rename', $item) }}"
                                    data-current-title="{{ $item->title }}">Rename</button>
                                <button type="button" class="btn btn-outline-dark move-file-btn"
                                    data-url="{{ route('role-files.move', $item) }}"
                                    data-current-folder="{{ dirname($item->file_path) }}">Move</button>
                                <button type="button" class="btn btn-outline-success access-file-btn"
                                    data-url="{{ route('role-files.access', $item) }}"
                                    data-scope="{{ $item->access_scope ?? 'role' }}"
                                    data-roles="{{ $item->allowed_roles_csv ?? '' }}"
                                    data-emails="{{ $item->allowed_emails_csv ?? '' }}"
                                    data-domains="{{ $item->allowed_domains_csv ?? '' }}">Akses</button>
                            </div>

                            <a href="{{ route(
                                'role-files.index',
                                array_filter([
                                    'role' => $activeRole,
                                    'q' => $search,
                                    'folder' => $selectedFolder,
                                    'role_filter' => $roleFilter,
                                    'view' => $viewMode,
                                    'log_action' => $logAction,
                                    'log_result' => $logResult,
                                    'log_file_id' => $item->id,
                                ]),
                            ) }}#audit-log"
                                class="btn btn-outline-secondary btn-sm btn-block mb-2">
                                Lihat Log
                            </a>

                            @if (!empty($item->share_token))
                                <input type="text" class="form-control form-control-sm mb-2 role-file-link"
                                    value="{{ route('role-files.shared', $item->share_token) }}" readonly>
                            @endif

                            <form method="POST" action="{{ route('role-files.destroy', $item) }}"
                                onsubmit="return confirm('Hapus file ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-block">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">Belum ada file role.</div>
                @endforelse
            </div>
        @else
            <div class="drive-table-wrap">
                <div class="px-3 py-2 bg-white border-bottom"><strong>Daftar File</strong></div>
                <div class="table-responsive p-0">
                    <table class="table table-sm table-hover mb-0 drive-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Judul</th>
                                <th>Akses</th>
                                <th>Kategori</th>
                                <th>Buku</th>
                                <th>Folder</th>
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
                                    <td>
                                        <span
                                            class="badge {{ $scopeClass($item->access_scope ?? 'role') }}">{{ $item->access_label ?? strtoupper($item->access_scope ?? 'role') }}</span>
                                        @if (!$item->can_access)
                                            <div class="small text-danger mt-1">Private untuk role/owner.</div>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($item->category) }}</td>
                                    <td>{{ optional($item->book)->judul ?? '-' }}</td>
                                    <td><span class="path-pill"
                                            title="{{ dirname($item->file_path) }}">{{ dirname($item->file_path) }}</span>
                                    </td>
                                    <td>{{ optional($item->uploader)->name ?? '-' }}</td>
                                    <td>{{ number_format($item->file_size / 1024, 1) }} KB</td>
                                    <td>
                                        @if ($item->can_access)
                                            <input type="text" class="form-control form-control-sm role-file-link"
                                                value="{{ route('role-files.preview', $item) }}" readonly>
                                        @else
                                            <span class="badge badge-secondary">Akses dibatasi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->can_access)
                                            <a href="{{ route('role-files.preview', $item) }}"
                                                class="btn btn-xs btn-outline-info" target="_blank">Preview</a>
                                            <a href="{{ route('role-files.download', $item) }}"
                                                class="btn btn-xs btn-outline-primary">Download</a>
                                            <button type="button"
                                                class="btn btn-xs btn-outline-secondary copy-link-btn">Copy Link</button>
                                        @else
                                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                                disabled>Preview</button>
                                        @endif

                                        <button type="button" class="btn btn-xs btn-outline-warning rename-file-btn"
                                            data-url="{{ route('role-files.rename', $item) }}"
                                            data-current-title="{{ $item->title }}">Rename</button>
                                        <button type="button" class="btn btn-xs btn-outline-dark move-file-btn"
                                            data-url="{{ route('role-files.move', $item) }}"
                                            data-current-folder="{{ dirname($item->file_path) }}">Move</button>
                                        <button type="button" class="btn btn-xs btn-outline-success access-file-btn"
                                            data-url="{{ route('role-files.access', $item) }}"
                                            data-scope="{{ $item->access_scope ?? 'role' }}"
                                            data-roles="{{ $item->allowed_roles_csv ?? '' }}"
                                            data-emails="{{ $item->allowed_emails_csv ?? '' }}"
                                            data-domains="{{ $item->allowed_domains_csv ?? '' }}">Akses</button>

                                        <a href="{{ route(
                                            'role-files.index',
                                            array_filter([
                                                'role' => $activeRole,
                                                'q' => $search,
                                                'folder' => $selectedFolder,
                                                'role_filter' => $roleFilter,
                                                'view' => $viewMode,
                                                'log_action' => $logAction,
                                                'log_result' => $logResult,
                                                'log_file_id' => $item->id,
                                            ]),
                                        ) }}#audit-log"
                                            class="btn btn-xs btn-outline-secondary">Lihat Log</a>

                                        @if (!empty($item->share_token))
                                            <input type="text" class="form-control form-control-sm mt-1 role-file-link"
                                                value="{{ route('role-files.shared', $item->share_token) }}" readonly>
                                        @endif

                                        <form method="POST" action="{{ route('role-files.destroy', $item) }}"
                                            class="d-inline" onsubmit="return confirm('Hapus file ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">Belum ada file role.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($files->hasPages())
            <div class="mt-2">{{ $files->links() }}</div>
        @endif

        <div class="card mt-3 border-0 shadow-sm" id="audit-log">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Audit Log Akses File</strong>
                <span class="small text-muted">120 log terbaru sesuai filter saat ini</span>
            </div>
            <div class="card-body">
                @if (!empty($selectedLogFile))
                    <div class="alert alert-info py-2">
                        Filter aktif untuk file: <strong>{{ $selectedLogFile->title }}</strong>
                        (Role: {{ strtoupper((string) $selectedLogFile->role) }}).
                        <a href="{{ route(
                            'role-files.index',
                            array_filter([
                                'role' => $activeRole,
                                'q' => $search,
                                'folder' => $selectedFolder,
                                'role_filter' => $roleFilter,
                                'view' => $viewMode,
                                'log_action' => $logAction,
                                'log_result' => $logResult,
                            ]),
                        ) }}#audit-log"
                            class="btn btn-xs btn-outline-primary ml-2">Reset Filter File</a>
                    </div>
                @endif

                <form method="GET" action="{{ route('role-files.index') }}" class="form-row mb-3">
                    <input type="hidden" name="q" value="{{ $search }}">
                    <input type="hidden" name="folder" value="{{ $selectedFolder }}">
                    <input type="hidden" name="role_filter" value="{{ $roleFilter }}">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    <input type="hidden" name="role" value="{{ $activeRole }}">
                    <input type="hidden" name="log_file_id" value="{{ $logFileId > 0 ? $logFileId : '' }}">

                    <div class="col-md-4 form-group mb-2">
                        <label class="mb-1">Aksi</label>
                        <select name="log_action" class="form-control">
                            <option value="">Semua aksi</option>
                            @foreach ($logActions as $action)
                                <option value="{{ $action }}" @selected($logAction === $action)>
                                    {{ strtoupper($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label class="mb-1">Hasil</label>
                        <select name="log_result" class="form-control">
                            <option value="" @selected($logResult === '')>Semua</option>
                            <option value="granted" @selected($logResult === 'granted')>Granted</option>
                            <option value="denied" @selected($logResult === 'denied')>Denied</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary btn-block">Filter Log</button>
                    </div>
                </form>

                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <div class="drive-stat">
                            <div class="label">Total Log</div>
                            <div class="value">{{ number_format($logSummary['total']) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="drive-stat">
                            <div class="label">Granted</div>
                            <div class="value text-success">{{ number_format($logSummary['granted']) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="drive-stat">
                            <div class="label">Denied</div>
                            <div class="value text-danger">{{ number_format($logSummary['denied']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Waktu</th>
                                <th>File</th>
                                <th>Aksi</th>
                                <th>User</th>
                                <th>Hasil</th>
                                <th>Scope</th>
                                <th>IP</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accessLogs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="font-weight-bold">
                                            {{ $log->file_title ?? '#' . $log->role_file_id }}</div>
                                        <small class="text-muted">role file:
                                            {{ strtoupper((string) ($log->file_role ?? '-')) }}</small>
                                    </td>
                                    <td>{{ strtoupper((string) $log->action) }}</td>
                                    <td>
                                        <div>{{ $log->user_name ?? '-' }}</div>
                                        <small class="text-muted">{{ $log->email ?? '-' }} /
                                            {{ strtoupper((string) ($log->role ?? '-')) }}</small>
                                    </td>
                                    <td>
                                        @if ($log->granted)
                                            <span class="badge badge-success">Granted</span>
                                        @else
                                            <span class="badge badge-danger">Denied</span>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper((string) ($log->scope ?? '-')) }}</td>
                                    <td>{{ $log->ip_address ?? '-' }}</td>
                                    <td>{{ $log->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada data log akses.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renameModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" id="renameForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Rename File</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Judul baru</label>
                    <input type="text" class="form-control" name="title" id="renameTitle" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="moveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" id="moveForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Move File</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Folder tujuan</label>
                    <input type="text" class="form-control" name="target_folder" id="moveFolder" required>
                    <small class="text-muted">Contoh: role-files/editor/books/ms-2026-001-judul-penulis</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">Pindahkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="accessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" id="accessForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Atur Akses File</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <label>Scope akses</label>
                    <select class="form-control" name="access_scope" id="accessScope" required>
                        <option value="private">Private (owner/superadmin)</option>
                        <option value="role">Role Terkait</option>
                        <option value="all_roles">Semua Role (internal)</option>
                        <option value="public">Publik (siapa saja)</option>
                    </select>

                    <label class="mt-2">Role tambahan yang diizinkan</label>
                    <select class="form-control" name="allowed_roles[]" id="allowedRoles" multiple size="6">
                        @foreach ($availableRoles as $role)
                            <option value="{{ $role }}">{{ strtoupper($role) }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih satu/lebih role yang tetap diizinkan meski scope private/role.</small>

                    <label class="mt-2">Email yang diizinkan (opsional)</label>
                    <textarea class="form-control" name="allowed_emails" id="allowedEmails" rows="2"
                        placeholder="pisahkan dengan koma, spasi, atau baris baru"></textarea>

                    <label class="mt-2">Domain email yang diizinkan (opsional)</label>
                    <textarea class="form-control" name="allowed_domains" id="allowedDomains" rows="2"
                        placeholder="contoh: kampus.ac.id perusahaan.com"></textarea>

                    <label class="mt-2">Masa berlaku link publik (hari, opsional)</label>
                    <input type="number" class="form-control" name="expires_days" min="1" max="365"
                        placeholder="Kosong = tanpa kedaluwarsa">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Akses</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const dropzone = document.getElementById('dropzone');
        const filesInput = document.getElementById('filesInput');
        const fileCounter = document.getElementById('fileCounter');

        function updateFileCounter() {
            const count = filesInput.files ? filesInput.files.length : 0;
            fileCounter.textContent = count > 0 ? `${count} file dipilih` : 'Belum ada file dipilih';
        }

        dropzone.addEventListener('click', function() {
            filesInput.click();
        });

        dropzone.addEventListener('dragover', function(event) {
            event.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function() {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(event) {
            event.preventDefault();
            dropzone.classList.remove('dragover');
            if (event.dataTransfer && event.dataTransfer.files) {
                filesInput.files = event.dataTransfer.files;
                updateFileCounter();
            }
        });

        filesInput.addEventListener('change', updateFileCounter);

        document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const container = btn.closest('tr') || btn.closest('.drive-grid-card');
                if (!container) return;
                const input = container.querySelector('.role-file-link');
                if (!input) return;

                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value);
                const oldText = btn.textContent;
                btn.textContent = 'Tersalin';
                setTimeout(function() {
                    btn.textContent = oldText;
                }, 1200);
            });
        });

        const renameForm = document.getElementById('renameForm');
        const renameTitle = document.getElementById('renameTitle');
        document.querySelectorAll('.rename-file-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                renameForm.action = btn.dataset.url;
                renameTitle.value = btn.dataset.currentTitle || '';
                $('#renameModal').modal('show');
            });
        });

        const moveForm = document.getElementById('moveForm');
        const moveFolder = document.getElementById('moveFolder');
        document.querySelectorAll('.move-file-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                moveForm.action = btn.dataset.url;
                moveFolder.value = btn.dataset.currentFolder || '';
                $('#moveModal').modal('show');
            });
        });

        const accessForm = document.getElementById('accessForm');
        const accessScope = document.getElementById('accessScope');
        const allowedRoles = document.getElementById('allowedRoles');
        const allowedEmails = document.getElementById('allowedEmails');
        const allowedDomains = document.getElementById('allowedDomains');

        function setMultiSelectValues(selectElement, csv) {
            const values = (csv || '').split(',').map(v => v.trim()).filter(Boolean);
            Array.from(selectElement.options).forEach(function(option) {
                option.selected = values.includes(option.value);
            });
        }

        document.querySelectorAll('.access-file-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                accessForm.action = btn.dataset.url;
                accessScope.value = btn.dataset.scope || 'role';
                setMultiSelectValues(allowedRoles, btn.dataset.roles || '');
                allowedEmails.value = btn.dataset.emails || '';
                allowedDomains.value = btn.dataset.domains || '';
                $('#accessModal').modal('show');
            });
        });
    </script>
@endsection
