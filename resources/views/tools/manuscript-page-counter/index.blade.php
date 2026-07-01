@extends('adminlte::page')

@section('title', 'Hitung Halaman Naskah')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0 text-dark">
            <i class="fas fa-calculator mr-2 text-primary"></i>
            Hitung Halaman Naskah Otomatis
        </h1>
        <small class="text-muted mt-2 mt-md-0">Setup margin rata: 2 cm (dengan pilihan ukuran kertas)</small>
    </div>
@stop

@section('content')
    <style>
        .paper-presets {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .paper-chip {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: all .15s ease;
        }

        .paper-chip:hover {
            border-color: #60a5fa;
            color: #1d4ed8;
        }

        .paper-chip.active {
            border-color: #2563eb;
            background: #dbeafe;
            color: #1e3a8a;
        }
    </style>

    @if (session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('danger') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold mb-0">Upload Naskah DOCX</h3>
                </div>
                <form action="{{ route('manuscript-page-counter.calculate') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Tool ini menghitung estimasi jumlah halaman dari dokumen naskah dengan aturan internal:
                            margin 2 cm dan ukuran kertas yang dapat dipilih.
                        </p>

                        <div class="form-group">
                            <label class="d-block">Preset Cepat Ukuran Kertas</label>
                            <div class="paper-presets" id="paper-presets">
                                @foreach ($paperOptions ?? ['A4' => 'A4', 'A5' => 'A5', 'B5' => 'B5', 'UNESCO' => 'UNESCO'] as $paperKey => $paperLabel)
                                    <button type="button" class="paper-chip" data-paper="{{ $paperKey }}">
                                        {{ $paperLabel }}
                                    </button>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">Klik chip untuk otomatis memilih ukuran utama.</small>
                        </div>

                        <div class="form-group">
                            <label for="paper_size">Ukuran Kertas <span class="text-danger">*</span></label>
                            <select id="paper_size" name="paper_size"
                                class="form-control @error('paper_size') is-invalid @enderror" required>
                                @foreach ($paperOptions ?? ['A4' => 'A4', 'A5' => 'A5', 'B5' => 'B5', 'UNESCO' => 'UNESCO'] as $paperKey => $paperLabel)
                                    <option value="{{ $paperKey }}" @selected(old('paper_size', $selectedPaper ?? 'A4') === $paperKey)>
                                        {{ $paperLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('paper_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="compare_paper_size">Bandingkan Dengan (opsional)</label>
                            <select id="compare_paper_size" name="compare_paper_size"
                                class="form-control @error('compare_paper_size') is-invalid @enderror">
                                <option value="">Tanpa perbandingan</option>
                                @foreach ($paperOptions ?? ['A4' => 'A4', 'A5' => 'A5', 'B5' => 'B5', 'UNESCO' => 'UNESCO'] as $paperKey => $paperLabel)
                                    <option value="{{ $paperKey }}" @selected(old('compare_paper_size', $comparePaper ?? '') === $paperKey)>
                                        {{ $paperLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('compare_paper_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hitung ukuran utama + ukuran pembanding dalam satu
                                submit.</small>
                        </div>

                        <div class="form-group">
                            <label for="manuscript_file">File Naskah (.docx) <span class="text-danger">*</span></label>
                            <input type="file" id="manuscript_file" name="manuscript_file"
                                class="form-control-file @error('manuscript_file') is-invalid @enderror" accept=".docx"
                                required>
                            @error('manuscript_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maksimal 50MB per file.</small>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <span class="small text-muted">Hasil perhitungan ditampilkan langsung tanpa menyimpan file ke
                            database.</span>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cogs mr-1"></i>Hitung Halaman
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold mb-0">Hasil Perhitungan</h3>
                </div>
                <div class="card-body">
                    @if (!empty($result))
                        <div class="mb-2"><strong>File:</strong> {{ $result['file_name'] }}</div>
                        <div class="mb-2"><strong>Setup:</strong> {{ $result['paper_label'] }}, margin
                            {{ $result['margin_cm'] }} cm</div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Jumlah Halaman</span>
                            <span class="badge badge-primary px-3 py-2">{{ $result['pages'] }}</span>
                        </div>

                        @if (!empty($result['compare']))
                            <div class="small text-muted mb-1">Perbandingan:</div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $result['compare']['paper_label'] }}</span>
                                <span class="badge badge-info px-3 py-2">{{ $result['compare']['pages'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Selisih vs utama</span>
                                <span
                                    class="badge {{ $result['compare']['diff'] >= 0 ? 'badge-warning' : 'badge-success' }} px-3 py-2">
                                    {{ $result['compare']['diff'] > 0 ? '+' : '' }}{{ $result['compare']['diff'] }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-file-upload fa-2x mb-2"></i><br>
                            Pilih ukuran kertas atau klik preset, lalu upload DOCX untuk melihat jumlah halaman otomatis.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function() {
            const paperSelect = document.getElementById('paper_size');
            const compareSelect = document.getElementById('compare_paper_size');
            const chips = Array.from(document.querySelectorAll('.paper-chip'));

            if (!paperSelect || chips.length === 0) {
                return;
            }

            const refreshActiveChip = () => {
                const selected = (paperSelect.value || '').toUpperCase();
                chips.forEach((chip) => {
                    const chipPaper = (chip.dataset.paper || '').toUpperCase();
                    chip.classList.toggle('active', chipPaper === selected);
                });
            };

            const syncCompareOptions = () => {
                if (!compareSelect) {
                    return;
                }

                const selected = (paperSelect.value || '').toUpperCase();

                Array.from(compareSelect.options).forEach((option) => {
                    const value = (option.value || '').toUpperCase();

                    if (!value) {
                        option.disabled = false;
                        return;
                    }

                    option.disabled = value === selected;
                });

                if ((compareSelect.value || '').toUpperCase() === selected) {
                    compareSelect.value = '';
                }
            };

            chips.forEach((chip) => {
                chip.addEventListener('click', () => {
                    paperSelect.value = chip.dataset.paper || paperSelect.value;
                    refreshActiveChip();
                    syncCompareOptions();
                });
            });

            paperSelect.addEventListener('change', () => {
                refreshActiveChip();
                syncCompareOptions();
            });

            refreshActiveChip();
            syncCompareOptions();
        })();
    </script>
@endsection
