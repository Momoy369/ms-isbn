@extends('adminlte::page')

@section('title', 'Tambah Naskah')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Naskah</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">

            {{-- Alert Error Global Opsional (Bisa dihapus jika lebih suka inline error di bawah) --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Terdapat Kesalahan!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card card-primary card-outline shadow">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-signature mr-1"></i> Form Tambah Naskah
                    </h3>
                </div>

                <form method="POST" action="{{ route('books.store') }}">
                    @csrf

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomor_naskah">Nomor Naskah <span class="text-danger">*</span></label>
                                    <input type="text" id="nomor_naskah" name="nomor_naskah"
                                        class="form-control @error('nomor_naskah') is-invalid @enderror"
                                        value="{{ old('nomor_naskah') }}" placeholder="Masukkan Nomor Naskah">
                                    @error('nomor_naskah')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="judul">Judul Naskah <span class="text-danger">*</span></label>
                                    <input type="text" id="judul" name="judul"
                                        class="form-control @error('judul') is-invalid @enderror"
                                        value="{{ old('judul') }}" placeholder="Masukkan Judul Naskah">
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="subjudul">Subjudul</label>
                                    <input type="text" id="subjudul" name="subjudul"
                                        class="form-control @error('subjudul') is-invalid @enderror"
                                        value="{{ old('subjudul') }}" placeholder="Masukkan Subjudul (Opsional)">
                                    @error('subjudul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penulis_1">Penulis Utama <span class="text-danger">*</span></label>
                                    <input type="text" id="penulis_1" name="penulis_1"
                                        class="form-control @error('penulis_1') is-invalid @enderror"
                                        value="{{ old('penulis_1') }}" placeholder="Nama Penulis Utama">
                                    @error('penulis_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="author_ktp_number">No. KTP Penulis <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="author_ktp_number" name="author_ktp_number"
                                        class="form-control @error('author_ktp_number') is-invalid @enderror"
                                        value="{{ old('author_ktp_number') }}" placeholder="16 digit KTP penulis" required>
                                    @error('author_ktp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Wajib diisi untuk naskah manual agar penulis bisa claim buku lewat portal.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="link_produk">Link Produk</label>
                                    <input type="text" id="link_produk" name="link_produk"
                                        class="form-control @error('link_produk') is-invalid @enderror"
                                        value="{{ old('link_produk') }}" placeholder="https://...">
                                    @error('link_produk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jumlah_cetak">Jumlah Cetak <span class="text-danger">*</span></label>
                                    <input type="number" id="jumlah_cetak" name="jumlah_cetak"
                                        class="form-control @error('jumlah_cetak') is-invalid @enderror"
                                        value="{{ old('jumlah_cetak') }}" placeholder="Contoh: 1000">
                                    @error('jumlah_cetak')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top text-right">
                        {{-- Opsional: Tombol batal untuk kembali ke halaman sebelumnya --}}
                        <a href="{{ url()->previous() }}" class="btn btn-default mr-2">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Simpan Naskah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
