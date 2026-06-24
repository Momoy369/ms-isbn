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
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('publishing-packages.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>
@endsection
