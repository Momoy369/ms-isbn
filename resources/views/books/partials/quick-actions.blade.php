<div class="card card-outline card-primary">

    <div class="card-header">

        <h5 class="mb-0">

            ISBN Workflow

        </h5>

    </div>


    <div class="card-body">

        @php
            $user = auth()->user();
            $canIsbnControl = in_array($user->role, ['admin', 'isbn', 'superadmin'], true);
            $missingAuditFiles = $book->missingIsbnAuditFiles();
            $missingPackageFiles = $book->missingIsbnPackageFiles();
            $canSubmitIsbn = $book->canSubmitIsbnToPerpusnas() && $canIsbnControl;
            $canApproveIsbn = $book->canApproveIsbnIssued() && $canIsbnControl;
            $canDownloadPackage = $book->canGenerateIsbnPackage() && empty($missingPackageFiles);
            $fileLabel = function ($type) {
                return match ($type) {
                    'cover' => 'Cover',
                    'skk' => 'SKK',
                    'halaman_judul' => 'Halaman Judul',
                    'surat_permohonan' => 'Surat Permohonan',
                    'copyright' => 'Copyright',
                    'naskah_final' => 'Naskah Final',
                    'attachment_isbn' => 'Attachment ISBN',
                    default => strtoupper(str_replace('_', ' ', $type)),
                };
            };
        @endphp

        <div class="alert alert-info">

            <strong>

                Status Saat Ini:

            </strong>

            {{ strtoupper(str_replace('_', ' ', $book->workflow_status)) }}

        </div>

        <div class="progress mb-3">

            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="
            width:
            {{ $book->progressPercent() }}%;
        ">

                {{ $book->progressPercent() }}%

            </div>

        </div>

        {{-- VALIDASI --}}

        <h6 class="text-primary">

            1. Validasi Naskah

        </h6>

        <form method="POST" action="{{ route('books.metadata.analyze', $book) }}">

            @csrf

            <button type="submit" class="btn btn-secondary btn-block">

                Analisis Metadata

            </button>

        </form>

        <form method="POST" action="{{ route('books.manuscript.analyze', $book) }}" class="mt-2">

            @csrf

            <button type="submit" class="btn btn-primary btn-block">

                Analisis Naskah

            </button>

        </form>

        <form method="POST" action="{{ route('books.lock-metadata', $book) }}" class="mt-2">

            @csrf

            <button class="btn btn-warning btn-block">

                Lock Metadata

            </button>

        </form>

        <form method="POST" action="{{ route('books.audit', $book) }}" class="mt-2">

            @csrf

            <button class="btn btn-dark btn-block">

                Jalankan Audit ISBN

            </button>

        </form>

        @if (!empty($missingAuditFiles))
            <small class="text-warning d-block mt-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Audit tetap bisa dijalankan, namun dokumen wajib masih kurang:
                {{ collect($missingAuditFiles)->map($fileLabel)->implode(', ') }}
            </small>
        @endif

        @if (auth()->user() && in_array(auth()->user()->role, ['admin', 'owner', 'finance', 'superadmin'], true))
            <a href="{{ route('external-sales.index', ['book_id' => $book->id]) }}#royalty-program"
                class="btn btn-outline-info btn-block mt-2">
                <i class="fas fa-coins mr-1"></i> Kurasi Royalti Buku Ini
            </a>
            <small class="text-muted d-block mt-1">
                Menu ini membuka panel admin untuk menyalakan distribusi, mengatur rate, dan mengunggah surat
                perjanjian.
            </small>
        @endif

        <hr>

        {{-- DOKUMEN ISBN --}}

        <h6 class="text-info">

            2. Dokumen ISBN

        </h6>

        <div class="row">

            <div class="col-6">

                <form method="POST" action="{{ route('books.generate.title-page', $book) }}">

                    @csrf

                    <button type="submit" class="btn btn-primary btn-block btn-sm">

                        Halaman Judul

                    </button>

                </form>

            </div>

            <div class="col-6">

                <form method="POST" action="{{ route('books.generate.copyright', $book) }}">

                    @csrf

                    <button type="submit" class="btn btn-info btn-block btn-sm">

                        Copyright

                    </button>

                </form>

            </div>

        </div>

        <div class="row mt-2">

            <div class="col-6">

                <form method="POST" action="{{ route('books.generate.request-letter', $book) }}">

                    @csrf

                    <button type="submit" class="btn btn-warning btn-block btn-sm">

                        Surat

                    </button>

                </form>

            </div>

            <div class="col-6">

                <form method="POST" action="{{ route('books.generate.attachment', $book) }}">

                    @csrf

                    <button type="submit" class="btn btn-danger btn-block btn-sm">

                        Attachment

                    </button>

                </form>

            </div>

        </div>

        <hr>

        {{-- PAKET ISBN --}}

        <h6 class="text-success">

            3. Paket ISBN

        </h6>

        <form method="POST" action="{{ route('books.generate-all', $book) }}">

            @csrf

            <button class="btn btn-success btn-block">

                Generate Paket ISBN

            </button>

        </form>

        <form method="POST" action="{{ route('books.generate-package', $book) }}" class="mt-2">

            @csrf

            <button class="btn btn-outline-success btn-block" @disabled(!$canDownloadPackage)>

                Download Paket ISBN

            </button>

        </form>

        @if (!$book->canGenerateIsbnPackage())
            <small class="text-muted d-block mt-2">
                Download paket aktif saat workflow mencapai READY FOR ISBN.
            </small>
        @endif

        @if (!empty($missingPackageFiles))
            <small class="text-warning d-block mt-1">
                Dokumen paket ISBN belum lengkap:
                {{ collect($missingPackageFiles)->map($fileLabel)->implode(', ') }}
            </small>
        @endif

        <hr>

        {{-- PERPUSNAS --}}

        <h6 class="text-danger">

            4. Perpusnas

        </h6>

        @if ($book->workflow_status === 'ready_for_isbn')
            <form method="POST" action="{{ route('books.submit-isbn', $book) }}">

                @csrf

                <button class="btn btn-danger btn-block" @disabled(!$canSubmitIsbn)>

                    Submit ke Perpusnas

                </button>

            </form>

            @if (!$canIsbnControl)
                <small class="text-muted d-block mt-2">
                    Aksi submit hanya untuk role Admin/ISBN/Superadmin.
                </small>
            @endif
        @endif

        @if ($book->workflow_status === 'isbn_submitted')
            <div class="card mt-3">

                <div class="card-header">

                    ISBN Telah Terbit

                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('books.approve-isbn', $book) }}">

                        @csrf

                        <div class="form-group">

                            <label>

                                Nomor ISBN

                            </label>

                            <input type="text" name="isbn" class="form-control" required>

                        </div>

                        <div class="form-group">

                            <label>

                                Tanggal Terbit

                            </label>

                            <input type="date" name="tanggal" class="form-control" required>

                        </div>

                        <button class="btn btn-success btn-block" @disabled(!$canApproveIsbn)>

                            Terbitkan ISBN

                        </button>

                    </form>

                    @if (!$canIsbnControl)
                        <small class="text-muted d-block mt-2">
                            Validasi/terbit ISBN hanya untuk role Admin/ISBN/Superadmin.
                        </small>
                    @endif

                </div>

            </div>
        @endif

        @if ($book->workflow_status === 'acc_penulis')
            <div class="alert alert-light border mb-0">
                <i class="fas fa-user-check mr-2 text-success"></i>
                Tahap ACC Penulis dilakukan dari portal author oleh penulis pemilik naskah.
            </div>
        @endif

    </div>

</div>
