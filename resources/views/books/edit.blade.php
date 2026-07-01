@extends('adminlte::page')

@section('title', 'Edit Buku')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Buku</h1>
@stop

@section('content')
    @php
        $selectedPackageId = old('publishing_package_id', $book->publishing_package_id);
        $selectedPackage = $packages->firstWhere('id', (int) $selectedPackageId);
        $editingIncluded = $selectedPackage ? (bool) $selectedPackage->includes_editing : true;
    @endphp
    <div class="row">
        <div class="col-12">

            {{-- Alert Error Global --}}
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
                        <i class="fas fa-edit mr-1"></i> Form Edit Buku
                    </h3>
                </div>

                <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nomor_naskah">Nomor Naskah</label>
                                    <input type="text" id="nomor_naskah" name="nomor_naskah"
                                        class="form-control @error('nomor_naskah') is-invalid @enderror"
                                        value="{{ old('nomor_naskah', $book->nomor_naskah) }}">
                                    @error('nomor_naskah')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="judul">Judul</label>
                                    <input type="text" id="judul" name="judul"
                                        class="form-control @error('judul') is-invalid @enderror"
                                        value="{{ old('judul', $book->judul) }}">
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="subjudul">Subjudul</label>
                                    <input type="text" id="subjudul" name="subjudul"
                                        class="form-control @error('subjudul') is-invalid @enderror"
                                        value="{{ old('subjudul', $book->subjudul) }}">
                                    @error('subjudul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="book_type">Jenis Buku</label>
                                    <select id="book_type" name="book_type"
                                        class="form-control @error('book_type') is-invalid @enderror">
                                        <option value="fiction" @selected(old('book_type', $book->book_type) == 'fiction')>Fiksi</option>
                                        <option value="nonfiction" @selected(old('book_type', $book->book_type) == 'nonfiction')>Non-Fiksi</option>
                                        <option value="poetry" @selected(old('book_type', $book->book_type) == 'poetry')>Poetry</option>
                                    </select>
                                    @error('book_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="penulis_1">Penulis Utama</label>
                                    <input type="text" id="penulis_1" name="penulis_1"
                                        class="form-control @error('penulis_1') is-invalid @enderror"
                                        value="{{ old('penulis_1', $book->penulis_1) }}">
                                    @error('penulis_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="author_ktp_number">No. KTP Penulis <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="author_ktp_number" name="author_ktp_number"
                                        class="form-control @error('author_ktp_number') is-invalid @enderror"
                                        value="{{ old('author_ktp_number', $book->author_ktp_number) }}" required>
                                    @error('author_ktp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Data KTP ini dipakai untuk pencocokan claim buku oleh akun penulis.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="isbn">ISBN</label>
                                    <input type="text" id="isbn" name="isbn"
                                        class="form-control @error('isbn') is-invalid @enderror"
                                        value="{{ old('isbn', $book->isbn) }}">
                                    @error('isbn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="tahun_copyright">Tahun Copyright</label>
                                        <input type="number" id="tahun_copyright" name="tahun_copyright"
                                            class="form-control @error('tahun_copyright') is-invalid @enderror"
                                            value="{{ old('tahun_copyright', $book->tahun_copyright) }}">
                                        @error('tahun_copyright')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="tahun_terbit">Tahun Terbit</label>
                                        <input type="number" id="tahun_terbit" name="tahun_terbit"
                                            class="form-control @error('tahun_terbit') is-invalid @enderror"
                                            value="{{ old('tahun_terbit', $book->tahun_terbit) }}">
                                        @error('tahun_terbit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="jumlah_halaman">Jumlah Halaman</label>
                                        <input type="text" id="jumlah_halaman" name="jumlah_halaman"
                                            class="form-control @error('jumlah_halaman') is-invalid @enderror"
                                            value="{{ old('jumlah_halaman', $book->jumlah_halaman) }}">
                                        @error('jumlah_halaman')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="ukuran_buku">Ukuran Buku</label>
                                        <input type="text" id="ukuran_buku" name="ukuran_buku"
                                            class="form-control @error('ukuran_buku') is-invalid @enderror"
                                            value="{{ old('ukuran_buku', $book->ukuran_buku) }}">
                                        @error('ukuran_buku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="manuscript_a4_pages">Halaman Mentah A4 (margin 2 cm) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" id="manuscript_a4_pages" name="manuscript_a4_pages"
                                            class="form-control @error('manuscript_a4_pages') is-invalid @enderror"
                                            value="{{ old('manuscript_a4_pages', $book->manuscript_a4_pages ?? $book->jumlah_halaman) }}"
                                            min="1" required>
                                        @error('manuscript_a4_pages')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="manuscript_file">Upload DOCX Naskah (opsional auto-hitungan)</label>
                                        <input type="file" id="manuscript_file" name="manuscript_file"
                                            class="form-control-file @error('manuscript_file') is-invalid @enderror"
                                            accept=".docx">
                                        @error('manuscript_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Jika DOCX diupload, sistem menghitung ulang A4/A5 otomatis dan menyimpan versi
                                            naskah final aktif.
                                        </small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="cetakan">Cetakan</label>
                                        <input type="text" id="cetakan" name="cetakan"
                                            class="form-control @error('cetakan') is-invalid @enderror"
                                            value="{{ old('cetakan', $book->cetakan) }}">
                                        @error('cetakan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="jumlah_cetak">Jumlah Cetak</label>
                                        <input type="number" id="jumlah_cetak" name="jumlah_cetak"
                                            class="form-control @error('jumlah_cetak') is-invalid @enderror"
                                            value="{{ old('jumlah_cetak', $book->jumlah_cetak) }}">
                                        @error('jumlah_cetak')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="link_produk">Link Produk</label>
                                    <input type="text" id="link_produk" name="link_produk"
                                        class="form-control @error('link_produk') is-invalid @enderror"
                                        value="{{ old('link_produk', $book->link_produk) }}">
                                    @error('link_produk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="selling_price">Harga Jual Manual</label>
                                        <input type="number" step="0.01" id="selling_price" name="selling_price"
                                            class="form-control @error('selling_price') is-invalid @enderror"
                                            value="{{ old('selling_price', $book->selling_price) }}">
                                        @error('selling_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Kosongkan untuk memakai default: jumlah halaman x Rp320 + 150%.
                                        </small>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="revision_fee_amount">Biaya Revisi Tambahan</label>
                                        <input type="number" step="0.01" id="revision_fee_amount"
                                            name="revision_fee_amount"
                                            class="form-control @error('revision_fee_amount') is-invalid @enderror"
                                            value="{{ old('revision_fee_amount', $book->revision_fee_amount) }}">
                                        @error('revision_fee_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Diinput admin. Dipakai mulai revisi ke-2 pada tahap yang sama.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="publishing_package_id">Paket Penerbitan</label>
                                    <select id="publishing_package_id" name="publishing_package_id"
                                        class="form-control @error('publishing_package_id') is-invalid @enderror">
                                        <option value="">-- Pilih Paket --</option>
                                        @foreach ($packages as $package)
                                            <option value="{{ $package->id }}"
                                                data-includes-editing="{{ $package->includes_editing ? '1' : '0' }}"
                                                @selected(old('publishing_package_id', $book->publishing_package_id) == $package->id)>
                                                {{ $package->name }}
                                                @if ($package->includes_editing)
                                                    <span class="text-success">(Editing)</span>
                                                @else
                                                    <span class="text-warning">(Tanpa Editing)</span>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('publishing_package_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr>
                                <h5 class="text-muted"><i class="fas fa-users mr-1"></i> Tim Produksi</h5>

                                <div class="form-group">
                                    <label for="designer">Desainer Sampul</label>
                                    <select id="designer" name="designer"
                                        class="form-control @error('designer') is-invalid @enderror">
                                        <option value="">-- Pilih Desainer --</option>
                                        @foreach ($designers as $designer)
                                            <option value="{{ $designer->name }}" @selected(old('designer', $book->designer) == $designer->name)>
                                                {{ $designer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-2">
                                    <label for="editor">Editor</label>
                                    <select id="editor" name="editor"
                                        class="form-control @error('editor') is-invalid @enderror"
                                        @disabled(!$editingIncluded)>
                                        <option value="">-- Pilih Editor --</option>
                                        @foreach ($editors as $editor)
                                            <option value="{{ $editor->name }}" @selected(old('editor', $book->editor) == $editor->name)>
                                                {{ $editor->name }} ({{ $editor->workload }} buku)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('editor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small id="editorHelp"
                                        class="form-text {{ $editingIncluded ? 'text-muted' : 'text-warning' }}">
                                        {{ $editingIncluded ? 'Pilih editor jika paket menyertakan layanan editing.' : 'Field editor dinonaktifkan karena paket ini tidak menyertakan layanan editing.' }}
                                    </small>
                                </div>
                                @if ($recommendedEditor)
                                    <div class="alert alert-info py-1 px-2 mb-3" style="font-size: 0.9rem;">
                                        <i class="fas fa-info-circle mr-1"></i> Rekomendasi:
                                        <strong>{{ $recommendedEditor->person_name }}</strong>
                                        ({{ $recommendedEditor->total }} buku aktif)
                                    </div>
                                @endif

                                <div class="form-group mb-2">
                                    <label for="layouter">Layouter</label>
                                    <select id="layouter" name="layouter"
                                        class="form-control @error('layouter') is-invalid @enderror">
                                        <option value="">-- Pilih Layouter --</option>
                                        @foreach ($layouters as $layouter)
                                            <option value="{{ $layouter->name }}" @selected(old('layouter', $book->layouter) == $layouter->name)>
                                                {{ $layouter->name }} ({{ $layouter->workload }} buku)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('layouter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if ($recommendedLayouter)
                                    <div class="alert alert-success py-1 px-2 mb-3" style="font-size: 0.9rem;">
                                        <i class="fas fa-check-circle mr-1"></i> Rekomendasi:
                                        <strong>{{ $recommendedLayouter->person_name }}</strong>
                                        ({{ $recommendedLayouter->total }} buku aktif)
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top text-right">
                        <a href="{{ url()->previous() }}" class="btn btn-default mr-2">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const packageSelect = document.getElementById('publishing_package_id');
            const editorSelect = document.getElementById('editor');
            const editorHelp = document.getElementById('editorHelp');

            if (!packageSelect || !editorSelect || !editorHelp) {
                return;
            }

            const syncEditorState = function() {
                const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                const includesEditing = !selectedOption || selectedOption.value === '' ?
                    true :
                    selectedOption.dataset.includesEditing === '1';

                editorSelect.disabled = !includesEditing;

                if (!includesEditing) {
                    editorSelect.value = '';
                    editorHelp.className = 'form-text text-warning';
                    editorHelp.textContent =
                        'Field editor dinonaktifkan karena paket ini tidak menyertakan layanan editing.';
                } else {
                    editorHelp.className = 'form-text text-muted';
                    editorHelp.textContent = 'Pilih editor jika paket menyertakan layanan editing.';
                }
            };

            packageSelect.addEventListener('change', syncEditorState);
            syncEditorState();
        });
    </script>
@endsection
