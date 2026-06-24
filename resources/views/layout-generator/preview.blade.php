@extends('adminlte::page')

@section('title', 'Preview Buku: ' . $book->judul)

@section('css')
<style>
    /* Styling khusus untuk area preview mirip PDF Viewer */
    .preview-container {
        background-color: #525659;
        padding: 40px 20px;
        border-radius: 0.25rem;
        overflow-y: auto;
    }
    .book-page {
        width: 148mm;
        min-height: 210mm;
        margin: 0 auto 40px auto; /* Memberikan jarak antar halaman */
        background: white;
        padding: 25mm;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        position: relative;
    }
    
    /* Pengaturan saat dicetak (CTRL+P) */
    @media print {
        .preview-container {
            background-color: transparent !important;
            padding: 0 !important;
        }
        .book-page {
            margin: 0 !important;
            box-shadow: none !important;
            page-break-after: always;
        }
        .no-print {
            display: none !important;
        }
        .main-footer, .main-header, .main-sidebar {
            display: none !important;
        }
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center no-print">
        <h1 class="m-0 text-dark">Pratinjau Layout</h1>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary shadow">
        <div class="card-header no-print">
            <h3 class="card-title">
                <i class="fas fa-book-open mr-1"></i> Preview: <strong>{{ $book->judul }}</strong>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak/PDF
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="preview-container">
                
                {{-- HALAMAN 1: COVER & JUDUL --}}
                <div class="book-page text-center">
                    <br><br><br><br><br>
                    @php
                        $cover = $book->getActiveFile('cover');
                    @endphp

                    @if ($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" class="img-fluid mb-4 shadow-sm" style="max-height:600px; object-fit: contain;">
                    @endif

                    <h1 class="font-weight-bold">{{ $book->judul }}</h1>
                    @if($book->subjudul)
                        <h4 class="text-muted">{{ $book->subjudul }}</h4>
                    @endif
                    <h5 class="mt-4">{{ $book->penulis_1 }}</h5>
                </div>

                {{-- HALAMAN 2: COPYRIGHT --}}
                <div class="book-page">
                    <h3 class="font-weight-bold mb-4">Copyright</h3>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td width="120" class="text-muted">ISBN</td>
                            <td>: <strong>{{ $book->isbn ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Editor</td>
                            <td>: {{ $book->editor ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Layouter</td>
                            <td>: {{ $book->layouter ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Designer</td>
                            <td>: {{ $book->designer ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                {{-- HALAMAN 3: DAFTAR ISI --}}
                <div class="book-page">
                    <h2 class="text-center font-weight-bold">DAFTAR ISI</h2>
                    <hr>
                    <ol style="line-height: 2;">
                        @php
                            $tocNumber = 1;
                        @endphp
                        @foreach ($book->sectionsGenerator->where('section_type', 'chapter') as $chapter)
                            <li>
                                <strong>BAB {{ $tocNumber }}</strong> - {{ strtoupper($chapter->title) }}
                            </li>
                            @php
                                $tocNumber++;
                            @endphp
                        @endforeach
                    </ol>
                </div>

                {{-- HALAMAN 4+: ISI BUKU DINAMIS --}}
                @php
                    $chapterNumber = 1;
                @endphp

                @foreach ($book->sectionsGenerator->sortBy('sort_order') as $section)
                    @switch($section->section_type)
                        
                        @case('preface')
                            <div class="book-page">
                                <h2 class="text-center font-weight-bold">KATA PENGANTAR</h2>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @break

                        @case('chapter')
                            <div class="book-page">
                                <h3 class="text-center text-muted">BAB {{ $chapterNumber }}</h3>
                                <h2 class="text-center font-weight-bold mb-4">{{ strtoupper($section->title) }}</h2>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @php
                                $chapterNumber++;
                            @endphp
                            @break

                        @case('subchapter')
                            <div class="book-page">
                                <h4 class="font-weight-bold">{{ $section->title }}</h4>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @break

                        @case('about_author')
                            <div class="book-page">
                                <h2 class="text-center font-weight-bold">TENTANG PENULIS</h2>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @break

                        @case('bibliography')
                            <div class="book-page">
                                <h2 class="text-center font-weight-bold">DAFTAR PUSTAKA</h2>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @break

                        @case('appendix')
                            <div class="book-page">
                                <h2 class="text-center font-weight-bold">LAMPIRAN</h2>
                                <hr>
                                {!! $section->content !!}
                            </div>
                            @break

                    @endswitch
                @endforeach

            </div>
            </div>
    </div>
@endsection