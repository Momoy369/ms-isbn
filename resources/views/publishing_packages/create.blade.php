@extends('adminlte::page')

@section('title', 'Tambah Paket Penerbitan')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Paket Penerbitan</h1>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('publishing-packages.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Paket</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" name="price" id="price" class="form-control" min="0" step="0.01"
                        value="0">
                </div>

                <div class="form-group">
                    <label>Kanal Publikasi</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="supports_print" value="1" class="custom-control-input"
                            id="supports_print" checked>
                        <label class="custom-control-label" for="supports_print">Cetak (masuk workspace percetakan)</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="supports_ebook" value="1" class="custom-control-input"
                            id="supports_ebook">
                        <label class="custom-control-label" for="supports_ebook">Ebook (masuk ebook publishing
                            system)</label>
                    </div>
                    @error('supports_print')
                        <small class="text-danger d-block">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Fitur Paket</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_editing" value="1" class="custom-control-input"
                            id="includes_editing" checked>
                        <label class="custom-control-label" for="includes_editing">Termasuk Editing</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_layout" value="1" class="custom-control-input"
                            id="includes_layout" checked>
                        <label class="custom-control-label" for="includes_layout">Termasuk Layout</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_cover_design" value="1" class="custom-control-input"
                            id="includes_cover_design" checked>
                        <label class="custom-control-label" for="includes_cover_design">Termasuk Cover Design</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_author_certificate" value="1"
                            class="custom-control-input" id="includes_author_certificate">
                        <label class="custom-control-label" for="includes_author_certificate">Sertifikat Penulis</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_google_scholar" value="1" class="custom-control-input"
                            id="includes_google_scholar">
                        <label class="custom-control-label" for="includes_google_scholar">Google Scholar</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="requires_hki_registration" value="1" class="custom-control-input"
                            id="requires_hki_registration">
                        <label class="custom-control-label" for="requires_hki_registration">Wajib Daftar HKI</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="default_print_quantity">Jumlah Cetak Default Paket</label>
                    <input type="number" name="default_print_quantity" id="default_print_quantity" class="form-control"
                        min="0" value="0">
                </div>

                <div class="form-group">
                    <label for="package_items">Daftar Item Paket (satu per baris)</label>
                    <textarea name="package_items" id="package_items" class="form-control" rows="6"
                        placeholder="Sertifikat Penulis&#10;Google Scholar&#10;Daftar HKI"></textarea>
                    <small class="form-text text-muted">Item ini akan muncul sebagai checklist untuk setiap naskah yang
                        memakai paket ini.</small>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('publishing-packages.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>
@endsection
