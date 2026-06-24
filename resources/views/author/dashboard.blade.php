@extends('adminlte::page')

@section('title', 'Portal Penulis')

@section('css')
    <style>
        .dashboard-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .dashboard-hero {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #fff;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.18);
        }

        .dashboard-hero .card-body {
            padding: 1.25rem 1.4rem;
        }

        .dashboard-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .dashboard-card .card-header {
            background: #fff;
            border-bottom: 1px solid #eef2f7;
        }

        .dashboard-card .card-body {
            padding: 1.15rem 1.25rem 1.25rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.42rem 0.8rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            background: #eef2ff;
            color: #4338ca;
        }

        .status-pill.is-review {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-pill.is-done {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pill.is-waiting {
            background: #f3f4f6;
            color: #4b5563;
        }

        .soft-panel {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fcfdff;
            padding: 1rem;
            height: 100%;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.8rem;
        }

        .section-title i {
            color: #2563eb;
        }

        .step-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.7rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #374151;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 0.2rem 0.25rem 0.2rem 0;
        }

        .step-chip.is-active {
            background: #dcfce7;
            color: #166534;
        }

        .review-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 1rem;
            background: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-shell">
        <div class="card dashboard-hero">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap align-items-center gap-3">
                    <div>
                        <h2 class="mb-1">Portal Penulis</h2>
                        <p class="mb-0">Pantau progres naskah, tim produksi, dan review dokumen dari satu tempat.</p>
                    </div>
                    <div class="text-right">
                        <div class="small text-uppercase fw-bold">Total buku</div>
                        <h3 class="mb-0">{{ $books->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        @if ($books->isEmpty())
            <div class="card dashboard-card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h4 class="mb-2">Belum ada buku yang sedang diproses</h4>
                    <p class="text-muted mb-0">Setelah buku ditugaskan, status dan review akan muncul di dashboard ini.</p>
                </div>
            </div>
        @else
            @foreach ($books as $book)
                @php
                    $statusClass = 'status-pill is-waiting';
                    if (in_array($book->workflow_status, ['editing_review', 'layout_review', 'cover_review'])) {
                        $statusClass = 'status-pill is-review';
                    } elseif ($book->workflow_status === 'completed' || $book->workflow_status === 'published') {
                        $statusClass = 'status-pill is-done';
                    }
                @endphp

                <div class="card dashboard-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h4 class="mb-1">{{ $book->judul }}</h4>
                                <div class="text-muted">Naskah: {{ $book->nomor_naskah }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="{{ $statusClass }}">{{ strtoupper($book->workflow_status) }}</span>
                                <span class="badge badge-light border">Progress {{ $book->progressPercent() }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Progress Produksi</strong>
                                <strong>{{ $book->progressPercent() }}%</strong>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    style="width: {{ $book->progressPercent() }}%;"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-8 mb-3">
                                <div class="soft-panel">
                                    <div class="section-title">
                                        <i class="fas fa-users"></i>
                                        <span>Tim Produksi</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <strong>Editor</strong>
                                            <div class="text-muted mt-1">
                                                {{ optional($book->assignments->where('role', 'editor')->first())->person_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <strong>Layouter</strong>
                                            <div class="text-muted mt-1">
                                                {{ optional($book->assignments->where('role', 'layouter')->first())->person_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <strong>Designer</strong>
                                            <div class="text-muted mt-1">
                                                {{ optional($book->assignments->where('role', 'designer')->first())->person_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <strong>ISBN</strong>
                                            <div class="text-muted mt-1">{{ $book->isbn ?? 'Belum Terbit' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="soft-panel">
                                    <div class="section-title">
                                        <i class="fas fa-route"></i>
                                        <span>Tahap Saat Ini</span>
                                    </div>
                                    <div class="fw-bold mb-2">{{ strtoupper($book->workflow_status) }}</div>
                                    <div class="text-muted small">Tahapan yang sedang berjalan untuk buku ini.</div>
                                </div>
                            </div>
                        </div>

                        <div class="soft-panel mb-4">
                            <div class="section-title">
                                <i class="fas fa-list-ol"></i>
                                <span>Progress Tahapan</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center">
                                @foreach (\App\Models\Book::WORKFLOWS as $index => $step)
                                    <span
                                        class="step-chip {{ $index <= $book->workflowIndex() ? 'is-active' : '' }}">{{ strtoupper($step) }}</span>
                                    @if (!$loop->last)
                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-7 mb-3">
                                <div class="review-card">
                                    <div class="section-title">
                                        <i class="fas fa-file-signature"></i>
                                        <span>Dokumen Produksi</span>
                                    </div>

                                    @if ($book->workflow_status === 'editing_review')
                                        <div class="alert alert-info mb-3">Hasil editing siap direview.</div>
                                        <form method="POST" action="{{ route('author.review.approve', $book) }}"
                                            class="mb-3">
                                            @csrf
                                            <input type="hidden" name="stage" value="editing">
                                            <button class="btn btn-success btn-sm">ACC Editing</button>
                                        </form>
                                        <form method="POST" enctype="multipart/form-data"
                                            action="{{ route('author.review.revision', $book) }}">
                                            @csrf
                                            <input type="hidden" name="stage" value="editing">
                                            <textarea name="note" class="form-control" rows="3" placeholder="Tulis catatan revisi" required></textarea>
                                            <input type="file" name="attachment" class="form-control mt-2">
                                            <button class="btn btn-danger mt-2 btn-sm">Minta Revisi</button>
                                        </form>
                                    @elseif ($book->workflow_status === 'layout_review')
                                        <div class="alert alert-info mb-3">Layout siap direview.</div>
                                        <form method="POST" action="{{ route('author.review.approve', $book) }}"
                                            class="mb-3">
                                            @csrf
                                            <input type="hidden" name="stage" value="layout">
                                            <button class="btn btn-success btn-sm">ACC Layout</button>
                                        </form>
                                        <form method="POST" enctype="multipart/form-data"
                                            action="{{ route('author.review.revision', $book) }}">
                                            @csrf
                                            <input type="hidden" name="stage" value="layout">
                                            <textarea name="note" class="form-control" rows="3" placeholder="Tulis catatan revisi" required></textarea>
                                            <input type="file" name="attachment" class="form-control mt-2">
                                            <button class="btn btn-danger mt-2 btn-sm">Revisi Layout</button>
                                        </form>
                                    @elseif ($book->workflow_status === 'cover_review')
                                        <div class="alert alert-info mb-3">Cover siap direview.</div>
                                        <form method="POST" action="{{ route('author.review.approve', $book) }}"
                                            class="mb-3">
                                            @csrf
                                            <input type="hidden" name="stage" value="cover">
                                            <button class="btn btn-success btn-sm">ACC Cover</button>
                                        </form>
                                        <form method="POST" enctype="multipart/form-data"
                                            action="{{ route('author.review.revision', $book) }}">
                                            @csrf
                                            <input type="hidden" name="stage" value="cover">
                                            <textarea name="note" class="form-control" rows="3" placeholder="Tulis catatan revisi" required></textarea>
                                            <input type="file" name="attachment" class="form-control mt-2">
                                            <button class="btn btn-danger mt-2 btn-sm">Revisi Cover</button>
                                        </form>
                                    @else
                                        <div class="alert alert-light border mb-0">Tidak ada aksi review yang dibutuhkan
                                            saat ini.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-5 mb-3">
                                <div class="review-card">
                                    <div class="section-title">
                                        <i class="fas fa-history"></i>
                                        <span>Riwayat Revisi</span>
                                    </div>

                                    @forelse ($book->reviews()->latest()->get() as $review)
                                        <div class="border rounded p-3 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong>{{ strtoupper($review->stage) }}</strong>
                                                @if ($review->status == 'approved')
                                                    <span class="badge badge-success">APPROVED</span>
                                                @else
                                                    <span class="badge badge-danger">REVISION</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mb-2">{{ $review->created_at }}</div>
                                            <div>{{ $review->note }}</div>
                                            @if ($review->attachment)
                                                <a href="{{ route('files.download', $review->attachment) }}"
                                                    class="btn btn-outline-secondary btn-sm mt-2">Lampiran
                                                    Revisi</a>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-muted">Belum ada riwayat revisi.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @php
                            $reviewFiles = collect();

                            if ($book->workflow_status === 'editing_review') {
                                $reviewFiles = $book->activeFiles->where('type', 'edited_manuscript');
                            } elseif ($book->workflow_status === 'layout_review') {
                                $reviewFiles = $book->activeFiles->where('type', 'layout_pdf');
                            } elseif ($book->workflow_status === 'cover_review') {
                                $reviewFiles = $book->activeFiles->where('type', 'cover_final');
                            }
                        @endphp

                        <div class="review-card mt-2">
                            <div class="section-title">
                                <i class="fas fa-folder-open"></i>
                                <span>Dokumen Review</span>
                            </div>

                            @forelse ($reviewFiles as $file)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                        <div>
                                            <strong>{{ strtoupper($file->type) }} V{{ $file->version }}</strong>
                                            <div class="text-muted small mt-1">{{ $file->original_name }}</div>
                                        </div>
                                        <a href="{{ route('files.download', $file) }}"
                                            class="btn btn-primary btn-sm">Download</a>
                                    </div>
                                    @if ($file->note)
                                        <div class="mt-2 small text-muted">
                                            <strong>{{ ucfirst($file->sender_role) }}</strong>: {{ $file->note }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted">Belum ada dokumen review.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
