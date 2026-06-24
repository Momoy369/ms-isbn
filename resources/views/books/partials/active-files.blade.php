<h4>Daftar Berkas</h4>

<table class="table table-bordered">

    <tr>
        <th>Jenis</th>
        <th>Nama File</th>
        <th>Aksi</th>
    </tr>

    @forelse ($book->activeFiles as $file)
        <tr>

            <td>
                {{ ucfirst(str_replace('_', ' ', $file->type)) }}
            </td>

            <td>
                {{ $file->original_name }}
            </td>

            <td>

                <a href="{{ route('files.download', $file) }}" class="btn btn-success btn-sm">

                    Download

                </a>

            </td>

        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center text-muted py-2">Belum ada file aktif.</td>
        </tr>
    @endforelse

</table>
