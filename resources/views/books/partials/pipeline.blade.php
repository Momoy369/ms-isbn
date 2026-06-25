<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-project-diagram mr-2"></i>Pipeline Produksi</h3>
    </div>

    <div class="card-body">
        @php
            $current = $book->workflowIndex();
            $dpPaid = $book->hasPaidInitialPackageInvoice();
            $dpWarning = $book->dpPaymentWarningMessage();
        @endphp

        {{-- STEPPER --}}
        <div class="d-flex flex-wrap justify-content-center align-items-start py-4" style="gap: 15px;">
            @foreach (\App\Models\Book::WORKFLOWS as $index => $step)
                @php
                    $isDone = $index < $current;
                    $isCurrent = $index == $current;

                    // Menentukan class visual berdasarkan status
                    $cardClass = $isCurrent
                        ? 'bg-gradient-primary text-white'
                        : ($isDone
                            ? 'bg-gradient-success text-white'
                            : 'bg-light text-muted');
                    $iconClass = $isCurrent ? 'fa-play-circle' : ($isDone ? 'fa-check-circle' : 'fa-circle');
                @endphp

                <div class="card {{ $cardClass }} shadow-none mb-0"
                    style="width: 150px; border: 1px solid rgba(0,0,0,0.1);">
                    <div class="card-body text-center p-3">
                        <div class="mb-2">
                            <i class="fas {{ $iconClass }} fa-2x"></i>
                        </div>
                        <strong class="d-block" style="font-size: 0.85rem;">
                            {{ strtoupper(str_replace('_', ' ', $step)) }}
                        </strong>
                        @if ($date = $book->workflowDate($step))
                            <small class="d-block mt-1 opacity-75">
                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                            </small>
                        @endif
                    </div>
                </div>

                @if (!$loop->last)
                    <div class="align-self-center text-muted d-none d-md-block">
                        <i class="fas fa-chevron-right fa-lg"></i>
                    </div>
                @endif
            @endforeach
        </div>

        <hr>

        {{-- STATUS AREA --}}
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="alert alert-light border shadow-sm mb-0">
                    <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.75rem;">Tahap
                        Aktif Saat Ini:</span>
                    <h5 class="m-0 font-weight-bold text-primary">
                        {{ strtoupper(str_replace('_', ' ', $book->workflow_status)) }}
                    </h5>
                </div>
                @unless ($dpPaid)
                    <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $dpWarning }}
                    </div>
                @endunless
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <form method="POST" action="{{ route('books.next-workflow', $book) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm"
                        @disabled(!$dpPaid)>
                        <i class="fas fa-forward mr-2"></i> Lanjutkan Tahap
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
