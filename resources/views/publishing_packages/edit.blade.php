@extends('adminlte::page')

@section('title', 'Edit Paket Penerbitan')

@section('content_header')
    <h1 class="m-0 text-dark">Edit Paket Penerbitan</h1>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('publishing-packages.update', $publishingPackage) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Paket</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="{{ old('name', $publishingPackage->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $publishingPackage->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" name="price" id="price" class="form-control" min="0" step="0.01"
                        value="{{ old('price', $publishingPackage->price) }}">
                </div>

                <div class="form-group">
                    <label>Fitur Paket</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_editing" value="1" class="custom-control-input"
                            id="includes_editing"
                            {{ old('includes_editing', $publishingPackage->includes_editing) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="includes_editing">Termasuk Editing</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_layout" value="1" class="custom-control-input"
                            id="includes_layout"
                            {{ old('includes_layout', $publishingPackage->includes_layout) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="includes_layout">Termasuk Layout</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="includes_cover_design" value="1" class="custom-control-input"
                            id="includes_cover_design"
                            {{ old('includes_cover_design', $publishingPackage->includes_cover_design) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="includes_cover_design">Termasuk Cover Design</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('publishing-packages.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>
@endsection
