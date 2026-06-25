<form method="POST" enctype="multipart/form-data" action="{{ route('books.files.store', $book) }}">

    @csrf

    <div class="form-group">

        <label>Jenis Berkas</label>

        <select name="type" class="form-control">

            <option value="naskah_final">
                Naskah Final
            </option>

            <option value="edited_manuscript">
                Hasil Editing (untuk Review Author)
            </option>

            <option value="cover">
                Cover
            </option>

            <option value="cover_final">
                Cover Final (untuk Review Author)
            </option>

            <option value="layout_pdf">
                PDF Layout (untuk Review Author)
            </option>

            <option value="skk">
                SKK
            </option>

            <option value="halaman_judul">
                Halaman Judul
            </option>

            <option value="surat_permohonan">
                Surat Permohonan
            </option>

            <option value="copyright">
                Copyright
            </option>

        </select>

    </div>

    <div class="form-group">

        <label>File</label>

        <input type="file" name="file" class="form-control" required>

    </div>

    <div class="form-group">

        <label>Catatan (opsional)</label>

        <input type="text" name="note" class="form-control" placeholder="Contoh: revisi bab 3 sudah diperbaiki">

    </div>

    <button type="submit" class="btn btn-success">

        Upload

    </button>

</form>
<hr>
