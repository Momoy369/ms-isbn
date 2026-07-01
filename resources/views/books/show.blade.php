@extends('adminlte::page')

@section('title', 'Detail Naskah')

@section('content')

    @if (session('success'))
        <div class="alert alert-success rounded-pill border-0 shadow-sm">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger rounded-pill border-0 shadow-sm">
            <i class="fas fa-times-circle mr-2"></i>
            {{ session('danger') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info rounded-pill border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i>
            {{ session('info') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning rounded-pill border-0 shadow-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('warning') }}
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 1rem; overflow: hidden;">

        @include('books.partials.header')

        <div class="card-body p-3 p-md-4">

            <div class="row g-3">

                @include('books.partials.pipeline')

                <div class="col-lg-4">

                    @include('books.partials.production-team')

                    @include('books.partials.approvals')

                    @include('books.partials.metadata-summary')

                    @include('books.partials.business-summary')

                    @include('books.partials.quick-actions')

                </div>

                <div class="col-lg-8">

                    <div class="accordion" id="bookDetailAccordion">

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-0 p-0">
                                <button class="btn btn-link btn-block text-left text-dark py-3 px-3" type="button"
                                    data-toggle="collapse" data-target="#collapseUpload" aria-expanded="true"
                                    aria-controls="collapseUpload">
                                    <i class="fas fa-cloud-upload-alt mr-2 text-primary"></i>
                                    Upload & Status Persiapan
                                </button>
                            </div>
                            <div id="collapseUpload" class="collapse show" data-parent="#bookDetailAccordion">
                                <div class="card-body p-3">
                                    @include('books.partials.upload-form')
                                    @include('books.partials.readiness')
                                    @include('books.partials.package-items')
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-0 p-0">
                                <button class="btn btn-link btn-block text-left text-dark py-3 px-3" type="button"
                                    data-toggle="collapse" data-target="#collapseCommunication" aria-expanded="true"
                                    aria-controls="collapseCommunication">
                                    <i class="fas fa-comments mr-2 text-info"></i>
                                    Komunikasi & Review
                                </button>
                            </div>
                            <div id="collapseCommunication" class="collapse show" data-parent="#bookDetailAccordion">
                                <div class="card-body p-3">
                                    @include('books.partials.production-chat', ['book' => $book])
                                    @include('books.partials.reviews')
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-0 p-0">
                                <button class="btn btn-link btn-block text-left text-dark py-3 px-3" type="button"
                                    data-toggle="collapse" data-target="#collapseHistory" aria-expanded="false"
                                    aria-controls="collapseHistory">
                                    <i class="fas fa-history mr-2 text-warning"></i>
                                    Riwayat & Audit
                                </button>
                            </div>
                            <div id="collapseHistory" class="collapse" data-parent="#bookDetailAccordion">
                                <div class="card-body p-3">
                                    @include('books.partials.audit-table')
                                    @include('books.partials.active-files')
                                    @include('books.partials.file-history')
                                    @include('books.partials.assignment-history')
                                    @include('books.partials.activity')
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
