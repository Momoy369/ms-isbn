<form method="POST" enctype="multipart/form-data" action="{{ route('books.files.store', $book) }}">

    @csrf

    <div class="form-group">

        <label>Jenis Berkas</label>

        <select name="type" class="form-control">

            <option value="naskah_final">
                Naskah Final
            </option>

            <option value="cover">
                Cover
            </option>

            <option value="skk">
                SKK
            </option>

        </select>

    </div>

    <div class="form-group">

        <label>File</label>

        <input type="file" name="file" class="form-control">

    </div>

    <button type="submit" class="btn btn-success">

        Upload

    </button>

</form>
<hr>
