@php

    $hasFailedAudit = $book->audits->where('passed', false)->count() > 0;

@endphp

<div class="card-header">

    <div class="
            d-flex
            justify-content-between
            align-items-center
        ">

        <div>

            <h3>

                {{ $book->judul }}

            </h3>

            @if ($book->nomor_surat)
                <div class="mt-2">

                    <small class="text-muted">

                        Nomor Surat:
                        <strong>

                            {{ $book->nomor_surat }}

                        </strong>

                    </small>

                </div>
            @endif

            @if ($book->subjudul)
                <small class="text-muted">

                    {{ $book->subjudul }}

                </small>
            @endif

        </div>

        <div>

            @if ($book->workflow_status === 'selesai')
                <span class="badge badge-primary">

                    SELESAI

                </span>
            @elseif($book->workflow_status === 'isbn_approved')
                <span class="badge badge-success">

                    ISBN APPROVED

                </span>
            @elseif($book->workflow_status === 'isbn_submitted')
                <span class="badge badge-warning">

                    ISBN SUBMITTED

                </span>
            @endif


        </div>

    </div>

</div>
