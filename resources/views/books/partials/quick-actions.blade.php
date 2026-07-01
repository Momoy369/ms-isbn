<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Action Center</h5>
        <span
            class="badge badge-light">{{ $workflowUi['statusLabel'] ?? strtoupper(str_replace('_', ' ', $book->workflow_status)) }}</span>
    </div>

    <div class="card-body">
        @php
            $canSubmitIsbn = (bool) ($workflowUi['canSubmitIsbn'] ?? false);
            $canApproveIsbn = (bool) ($workflowUi['canApproveIsbn'] ?? false);
            $canDownloadPackage = (bool) ($workflowUi['canDownloadPackage'] ?? false);
            $isAuthorOwner = (bool) ($workflowUi['isAuthorOwner'] ?? false);
            $primaryMode = (string) ($workflowUi['primaryMode'] ?? 'next');
            $primaryDisabled = (bool) ($workflowUi['primaryDisabled'] ?? false);
            $blockers = $workflowUi['blockers'] ?? [];
            $missingAuditFiles = $workflowUi['missingAuditFiles'] ?? [];
            $missingPackageFiles = $workflowUi['missingPackageFiles'] ?? [];
            $missingAuditFileLabels = $workflowUi['missingAuditFileLabels'] ?? [];
            $missingPackageFileLabels = $workflowUi['missingPackageFileLabels'] ?? [];
            $canViewRoyaltyCuration = (bool) ($workflowUi['canViewRoyaltyCuration'] ?? false);
            $progressPercent = (int) ($workflowUi['progressPercent'] ?? $book->progressPercent());
        @endphp

        <div class="alert alert-info mb-3">
            <strong>Status saat ini:</strong>
            {{ $workflowUi['statusLabel'] ?? strtoupper(str_replace('_', ' ', $book->workflow_status)) }}
            <small class="d-block mt-1 text-muted">
                Mode baru: aksi utama sudah menggunakan orkestrasi workflow terpusat. Gunakan "Aksi Manual Lanjutan"
                hanya saat diperlukan.
            </small>
            <div class="progress mt-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                    style="width: {{ $progressPercent }}%;">
                    {{ $progressPercent }}%
                </div>
            </div>
        </div>

        <h6 class="text-primary">Aksi Utama</h6>

        @if ($primaryMode === 'done')
            <div class="alert alert-secondary mb-3">
                Workflow sudah selesai. Tidak ada aksi utama lanjutan.
            </div>
        @elseif ($primaryMode === 'approve_isbn')
            <div class="card border mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('books.workflow.execute-primary', $book) }}">
                        @csrf
                        <div class="form-group mb-2">
                            <label class="mb-1">Nomor ISBN</label>
                            <input type="text" name="isbn" class="form-control" required>
                        </div>
                        <div class="form-group mb-2">
                            <label class="mb-1">Tanggal Terbit</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <button class="btn btn-success btn-block" @disabled($primaryDisabled || !$canApproveIsbn)>
                            Verifikasi dan Terbitkan ISBN
                        </button>
                    </form>
                </div>
            </div>
        @elseif ($primaryMode === 'submit')
            <form method="POST" action="{{ route('books.workflow.execute-primary', $book) }}" class="mb-3">
                @csrf
                <button class="btn btn-danger btn-block" @disabled($primaryDisabled || !$canSubmitIsbn)>
                    Submit ke Perpusnas
                </button>
            </form>
        @elseif ($primaryMode === 'audit')
            <form method="POST" action="{{ route('books.workflow.execute-primary', $book) }}" class="mb-3">
                @csrf
                <button class="btn btn-dark btn-block" @disabled($primaryDisabled)>
                    Jalankan Audit ISBN
                </button>
            </form>
        @elseif ($primaryMode === 'author_approval')
            @if ($isAuthorOwner)
                <form method="POST" action="{{ route('books.workflow.execute-primary', $book) }}" class="mb-3">
                    @csrf
                    <button class="btn btn-success btn-block" @disabled($primaryDisabled)>
                        Setujui Naskah (Author)
                    </button>
                </form>
            @else
                <div class="alert alert-light border mb-3">
                    Tahap ACC Penulis dilakukan dari portal author oleh penulis pemilik naskah.
                </div>
            @endif
        @else
            <form method="POST" action="{{ route('books.workflow.execute-primary', $book) }}" class="mb-3">
                @csrf
                <button class="btn btn-primary btn-block" @disabled($primaryDisabled)>
                    Lanjutkan Tahap
                </button>
            </form>
        @endif

        <h6 class="text-warning">Blockers & Warning</h6>
        @if (empty($blockers) && empty($missingAuditFiles) && empty($missingPackageFiles))
            <div class="alert alert-success mb-3">Tidak ada blocker kritikal saat ini.</div>
        @else
            <div class="alert alert-warning mb-3">
                <ul class="mb-0 pl-3">
                    @foreach ($blockers as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                    @if (!empty($missingAuditFiles))
                        <li>Dokumen audit belum lengkap: {{ implode(', ', $missingAuditFileLabels) }}</li>
                    @endif
                    @if (!empty($missingPackageFiles))
                        <li>Dokumen paket ISBN belum lengkap: {{ implode(', ', $missingPackageFileLabels) }}</li>
                    @endif
                </ul>
            </div>
        @endif

        <div id="secondaryActionsAccordion">
            <div class="card border-0 shadow-none">
                <div class="card-header bg-white px-0 py-1 border-0" id="headingSecondaryActions">
                    <button class="btn btn-link px-0" data-toggle="collapse" data-target="#collapseSecondaryActions"
                        aria-expanded="false" aria-controls="collapseSecondaryActions">
                        Aksi Manual Lanjutan
                    </button>
                </div>
                <div id="collapseSecondaryActions" class="collapse" aria-labelledby="headingSecondaryActions"
                    data-parent="#secondaryActionsAccordion">
                    <div class="card-body px-0 pb-0">
                        <h6 class="text-primary">Validasi Naskah</h6>
                        <form method="POST" action="{{ route('books.metadata.analyze', $book) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-block">Analisis Metadata</button>
                        </form>

                        <form method="POST" action="{{ route('books.manuscript.analyze', $book) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block">Analisis Naskah</button>
                        </form>

                        <form method="POST" action="{{ route('books.lock-metadata', $book) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-warning btn-block">Lock Metadata</button>
                        </form>

                        <hr>
                        <h6 class="text-info">Dokumen ISBN</h6>

                        <div class="row">
                            <div class="col-6">
                                <form method="POST" action="{{ route('books.generate.title-page', $book) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-block btn-sm">Halaman
                                        Judul</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <form method="POST" action="{{ route('books.generate.copyright', $book) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block btn-sm">Copyright</button>
                                </form>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-6">
                                <form method="POST" action="{{ route('books.generate.request-letter', $book) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-block btn-sm">Surat</button>
                                </form>
                            </div>
                            <div class="col-6">
                                <form method="POST" action="{{ route('books.generate.attachment', $book) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block btn-sm">Attachment</button>
                                </form>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-success">Paket ISBN</h6>

                        <form method="POST" action="{{ route('books.workflow.prepare-isbn', $book) }}">
                            @csrf
                            <button class="btn btn-outline-dark btn-block">
                                Prepare ISBN Otomatis (Generate + Audit)
                            </button>
                        </form>

                        <form method="POST" action="{{ route('books.generate-all', $book) }}">
                            @csrf
                            <button class="btn btn-success btn-block">Generate Paket ISBN</button>
                        </form>

                        <form method="POST" action="{{ route('books.generate-package', $book) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-outline-success btn-block" @disabled(!$canDownloadPackage)>
                                Download Paket ISBN
                            </button>
                        </form>

                        @if ($canViewRoyaltyCuration)
                            <a href="{{ route('external-sales.index', ['book_id' => $book->id]) }}#royalty-program"
                                class="btn btn-outline-info btn-block mt-3">
                                <i class="fas fa-coins mr-1"></i> Kurasi Royalti Buku Ini
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
