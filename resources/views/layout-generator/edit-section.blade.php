@extends('adminlte::page')

@section('title', 'Edit Bagian: ' . $section->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Edit Konten Buku</h1>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-edit text-primary mr-1"></i> {{ $section->title }}
            </h3>
        </div>

        {{-- FORM START --}}
        <form method="POST" action="{{ route('layout-generator.section.update', $section) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label for="title">Judul Bagian <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $section->title) }}" required>

                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-0">
                    <label for="editor">Isi Konten <span class="text-danger">*</span></label>
                    <textarea id="editor" name="content">{!! old('content', $section->content) !!}</textarea>

                    @error('content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="card-footer bg-light text-right">
                <a href="{{ url()->previous() }}" class="btn btn-default mr-2">
                    <i class="fas fa-times mr-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
        {{-- FORM END --}}

    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#editor',
            height: 600,
            /* Ketinggian ditambah sedikit agar lebih nyaman untuk mengetik naskah */
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | table image media | fullscreen preview',
            branding: false, // Menghilangkan watermark tinymce
            promotion: false, // Menghilangkan tombol upgrade
            setup: function(editor) {
                // Memastikan teks tersimpan ke textarea saat terjadi perubahan
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
    </script>
@endsection
