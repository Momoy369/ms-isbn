<h4>Riwayat Versi Berkas</h4>

@php

    $groupedFiles = $book->fileHistories->groupBy('type');

@endphp

@foreach ($groupedFiles as $type => $files)
    <div class="card mb-3">

        <div class="card-header">

            {{ ucfirst(str_replace('_', ' ', $type)) }}

        </div>

        <div class="card-body">

            <table class="table">

                <tr>
                    <th>File</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>

                @foreach ($files as $file)
                    <tr>

                        <td>

                            {{ $file->original_name }}

                        </td>

                        <td>

                            @if ($file->is_active)
                                <span class="badge badge-success">

                                    ACTIVE

                                </span>
                            @else
                                <span class="badge badge-secondary">

                                    ARCHIVE

                                </span>
                            @endif

                        </td>

                        <td>

                            {{ $file->created_at }}

                        </td>

                        <td>

                            @if (!$file->is_active)
                                <form method="POST" action="{{ route('files.restore', $file) }}">

                                    @csrf

                                    <button class="btn btn-warning btn-sm">

                                        Restore

                                    </button>

                                </form>
                            @endif

                        </td>

                    </tr>
                @endforeach

            </table>

        </div>

    </div>
@endforeach
