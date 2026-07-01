<form method="POST" enctype="multipart/form-data" action="{{ route('books.files.store', $book) }}">

    @csrf

    @php
        $isFinished = $book->workflow_status === 'selesai';
        $canUploadFinalFiles = auth()->check() && in_array(auth()->user()->role, ['admin', 'isbn', 'superadmin'], true);
        $finalTypeLabels = [
            'isbn_image' => 'ISBN Image',
            'qrcbn_image' => 'QRCBN Image',
            'final_layout' => 'Final Layout',
            'final_cover' => 'Final Cover',
        ];
        $finalReady = collect($finalTypeLabels)->filter(fn($_, $type) => (bool) $book->getActiveFile($type))->count();
    @endphp

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-success">Kelengkapan Paket Final Author</h6>
                <span class="badge badge-success">{{ $finalReady }}/{{ count($finalTypeLabels) }} lengkap</span>
            </div>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                    style="width: {{ (int) round(($finalReady / count($finalTypeLabels)) * 100) }}%;"
                    aria-valuenow="{{ $finalReady }}" aria-valuemin="0" aria-valuemax="{{ count($finalTypeLabels) }}">
                </div>
            </div>
            <div class="d-flex flex-wrap">
                @foreach ($finalTypeLabels as $type => $label)
                    @php($ready = (bool) $book->getActiveFile($type))
                    <span class="mr-2 mb-1 badge {{ $ready ? 'badge-success' : 'badge-secondary' }}">
                        {{ $label }}: {{ $ready ? 'siap' : 'belum' }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    @if ($isFinished)
        <div class="alert alert-info">
            Workflow naskah sudah <strong>SELESAI</strong>. Upload berkas review author (editing/layout/cover review)
            dinonaktifkan agar pipeline tidak kembali ke tahap sebelumnya.
        </div>
    @endif

    @if ($canUploadFinalFiles)
        <div class="alert alert-success">
            Role Anda berwenang upload paket final author: ISBN Image, QRCBN Image, Final Layout, dan Final Cover.
        </div>
    @endif

    <div class="form-group">

        <label>Jenis Berkas</label>

        <select name="type" class="form-control">

            <option value="naskah_final">
                Naskah Final
            </option>

            @if (!$isFinished)
                <option value="edited_manuscript">
                    Hasil Editing (untuk Review Author)
                </option>
            @endif

            <option value="cover">
                Cover
            </option>

            @if (!$isFinished)
                <option value="cover_final">
                    Cover Final (untuk Review Author)
                </option>
            @endif

            @if (!$isFinished)
                <option value="layout_pdf">
                    PDF Layout (untuk Review Author)
                </option>
            @endif

            <option value="skk">
                SKK
            </option>

            <option value="surat_permohonan">
                Surat Permohonan
            </option>

            <option value="copyright">
                Copyright
            </option>

            <option disabled>──────── Berkas Final Author ────────</option>
            <option value="skk">SKK</option>
            <option value="hki">HKI (opsional)</option>
            <option value="sertifikat_penulis">Sertifikat Penulis</option>
            <option value="isbn_image">ISBN Image</option>
            <option value="qrcbn_image">QRCBN Image</option>
            <option value="final_layout">Final Layout</option>
            <option value="final_cover">Final Cover</option>

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
