@extends('adminlte::page')

@section('title', 'Manajemen Layout')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Detail Layout Buku</h1>
        <a href="{{ route('layout-generator.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow">

        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                @php
                    $bookTypeLabel =
                        [
                            'fiction' => 'FIKSI',
                            'nonfiction' => 'NON-FIKSI',
                            'poetry' => 'PUISI',
                        ][$book->book_type] ?? strtoupper((string) $book->book_type);

                    $bookTypeBadge =
                        [
                            'fiction' => 'primary',
                            'nonfiction' => 'success',
                            'poetry' => 'warning',
                        ][$book->book_type] ?? 'secondary';
                @endphp

                <span class="badge badge-{{ $bookTypeBadge }} mr-2">
                    <i class="fas fa-bookmark mr-1"></i> {{ $bookTypeLabel }}
                </span>
                <strong>{{ $book->judul }}</strong>
            </h3>
            <div class="card-tools">
                <a href="{{ route('layout-generator.preview', $book) }}" class="btn btn-info btn-sm mr-1">
                    <i class="fas fa-eye mr-1"></i> Preview Layout
                </a>
                <a href="{{ route('layout-generator.generate', $book) }}"
                    class="btn btn-success btn-sm {{ !$isReadyForLayout ? 'disabled' : '' }}"
                    {{ !$isReadyForLayout ? 'aria-disabled=true tabindex=-1' : '' }}>
                    <i class="fas fa-file-word mr-1"></i> Generate DOCX
                </a>
            </div>
        </div>

        {{-- CARD BODY --}}
        <div class="card-body">

            {{-- ALERT STATUS NASKAH --}}
            @if ($isReadyForLayout)
                <div class="alert alert-success alert-dismissible rounded">
                    <h5><i class="icon fas fa-check-circle"></i> Siap Di-generate!</h5>
                    Naskah sudah memenuhi semua syarat dan siap untuk diubah menjadi layout DOCX.
                </div>
            @else
                <div class="alert alert-warning alert-dismissible rounded">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Belum Siap Generate</h5>
                    Naskah belum memenuhi syarat kelengkapan layout. Silakan periksa bagian validasi di bawah.
                    @if (!empty($missingRequirements) && $missingRequirements->isNotEmpty())
                        <hr>
                        <p class="mb-1"><strong>Yang masih perlu dilengkapi:</strong></p>
                        <ul class="mb-0 pl-3">
                            @foreach ($missingRequirements as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success rounded">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning rounded">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger rounded">
                    <h6 class="mb-1"><i class="fas fa-times-circle mr-1"></i> Periksa input Anda:</h6>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SMALL BOXES (STATISTIK) --}}
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3>{{ number_format($totalWords) }}</h3>
                            <p>Total Jumlah Kata</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3>{{ $estimatedPages }}</h3>
                            <p>Estimasi Halaman</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3>{{ $book->sectionsGenerator->count() }}</h3>
                            <p>Jumlah Section / Bagian</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VALIDASI KESIAPAN --}}
            <div class="card bg-light shadow-none border mb-4">
                <div class="card-header border-0">
                    <h3 class="card-title text-muted"><i class="fas fa-tasks mr-1"></i> Validasi Kesiapan Layout</h3>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        @php
                            $validations = [
                                'judul' => 'Judul',
                                'penulis' => 'Penulis',
                                'isbn' => 'ISBN',
                                'cover' => 'Cover',
                                'kata_pengantar' => 'Pengantar',
                                'tentang_penulis' => 'Profil Penulis',
                                'isi_utama' => 'Isi Utama',
                            ];
                        @endphp

                        @foreach ($validations as $key => $label)
                            <div class="mr-3 mb-3">
                                <span
                                    class="badge badge-pill badge-{{ $validation[$key] ? 'success' : 'danger' }} px-3 py-2"
                                    style="font-size: 0.9rem;">
                                    <i class="fas fa-{{ $validation[$key] ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                    {{ $label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-white border shadow-none mb-4">
                <div class="card-header bg-light border-0">
                    <h3 class="card-title text-muted mb-0">
                        <i class="fas fa-chart-pie mr-1"></i> Komposisi Section Saat Ini
                    </h3>
                </div>
                <div class="card-body py-2">
                    @if ($sectionBreakdown->isEmpty())
                        <p class="text-muted mb-0">Belum ada section. Tambahkan section agar layout bisa disusun.</p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach ($sectionBreakdown as $type => $count)
                                <span class="badge badge-light border mr-2 mb-2 px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}: <strong>{{ $count }}</strong>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row mt-4">
                {{-- KIRI: STRUKTUR BUKU --}}
                <div class="col-md-8">
                    <h5 class="font-weight-bold mb-3">
                        <i class="fas fa-list-ol mr-1"></i> Struktur Buku
                    </h5>

                    @if ($book->sectionsGenerator->isEmpty())
                        <div class="text-center text-muted py-4 border rounded bg-light">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">Belum ada bagian buku yang ditambahkan.</p>
                        </div>
                    @else
                        <ul class="list-group shadow-sm">
                            @foreach ($book->sectionsGenerator as $section)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $section->title }}</h6>

                                        @switch($section->section_type)
                                            @case('preface')
                                                <span class="badge badge-success">Kata Pengantar</span>
                                            @break

                                            @case('chapter')
                                                <span class="badge badge-primary">Bab</span>
                                            @break

                                            @case('subchapter')
                                                <span class="badge badge-info">Sub Bab</span>
                                            @break

                                            @case('about_author')
                                                <span class="badge badge-warning">Tentang Penulis</span>
                                            @break

                                            @case('bibliography')
                                                <span class="badge badge-secondary">Daftar Pustaka</span>
                                            @break

                                            @case('appendix')
                                                <span class="badge badge-dark">Lampiran</span>
                                            @break

                                            @default
                                                <span class="badge badge-light">{{ $section->section_type }}</span>
                                        @endswitch
                                    </div>

                                    {{-- AKSI --}}
                                    <div class="d-flex align-items-center">
                                        <div class="btn-group btn-group-sm mr-2">
                                            <form method="POST"
                                                action="{{ route('layout-generator.section.up', $section) }}"
                                                class="m-0">
                                                @csrf
                                                <button class="btn btn-default text-secondary" title="Geser ke Atas"
                                                    {{ $loop->first ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('layout-generator.section.down', $section) }}"
                                                class="m-0">
                                                @csrf
                                                <button class="btn btn-default text-secondary" title="Geser ke Bawah"
                                                    {{ $loop->last ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <a href="{{ route('layout-generator.section.edit', $section) }}"
                                            class="btn btn-warning btn-sm mr-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('layout-generator.section.delete', $section) }}"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus bagian ini?')"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- KANAN: TAMBAH SECTION --}}
                <div class="col-md-4 mt-4 mt-md-0">
                    <div class="card card-outline card-primary shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-light">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Bagian
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('layout-generator.section.store', $book) }}">
                                @csrf

                                <div class="form-group">
                                    <label>Jenis Bagian <span class="text-danger">*</span></label>
                                    <select name="section_type"
                                        class="form-control @error('section_type') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        @if ($book->book_type === 'fiction')
                                            <option value="preface"
                                                {{ old('section_type') === 'preface' ? 'selected' : '' }}>Kata Pengantar
                                            </option>
                                            <option value="chapter"
                                                {{ old('section_type') === 'chapter' ? 'selected' : '' }}>Bab</option>
                                            <option value="subchapter"
                                                {{ old('section_type') === 'subchapter' ? 'selected' : '' }}>Sub Bab
                                            </option>
                                            <option value="author"
                                                {{ old('section_type') === 'author' ? 'selected' : '' }}>Tentang Penulis
                                            </option>
                                            <option value="bibliography"
                                                {{ old('section_type') === 'bibliography' ? 'selected' : '' }}>Daftar
                                                Pustaka</option>
                                        @else
                                            <option value="preface"
                                                {{ old('section_type') === 'preface' ? 'selected' : '' }}>Kata Pengantar
                                            </option>
                                            <option value="foreword"
                                                {{ old('section_type') === 'foreword' ? 'selected' : '' }}>Prakata</option>
                                            <option value="chapter"
                                                {{ old('section_type') === 'chapter' ? 'selected' : '' }}>Bab</option>
                                            <option value="subchapter"
                                                {{ old('section_type') === 'subchapter' ? 'selected' : '' }}>Sub Bab
                                            </option>
                                            <option value="bibliography"
                                                {{ old('section_type') === 'bibliography' ? 'selected' : '' }}>Daftar
                                                Pustaka</option>
                                            <option value="appendix"
                                                {{ old('section_type') === 'appendix' ? 'selected' : '' }}>Lampiran
                                            </option>
                                            <option value="author"
                                                {{ old('section_type') === 'author' ? 'selected' : '' }}>Tentang Penulis
                                            </option>
                                        @endif
                                    </select>
                                    @error('section_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Gunakan section yang relevan agar hasil DOCX terstruktur dan mudah diproses tim
                                        produksi.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Judul Bagian <span class="text-danger">*</span></label>
                                    <input type="text" name="title"
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title') }}" placeholder="Contoh: Bab 1 Pendahuluan" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Tambah
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
