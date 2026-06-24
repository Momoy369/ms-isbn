<h4>Daftar Berkas</h4>

                    <table class="table table-bordered">

                        <tr>
                            <th>Jenis</th>
                            <th>Nama File</th>
                            <th>Aksi</th>
                        </tr>

                        @foreach ($book->activeFiles as $file)
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
                        @endforeach

                    </table>