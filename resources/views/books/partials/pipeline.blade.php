<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-project-diagram mr-2"></i>Pipeline Produksi</h3>
    </div>

    <div class="card-body">
        @php
            $parallelService = app(\App\Services\ParallelWorkflowService::class);
            $steps = $book->workflowSteps();
            $dpPaid = $book->hasPaidInitialPackageInvoice();
            $dpWarning = $book->dpPaymentWarningMessage();
            $isFinished = $book->workflow_status === 'selesai';
            $canAdvance = $dpPaid && !$isFinished;
            $authorRegistered = $parallelService->isAuthorRegistered($book);
            $pipelineStepStatus = $workflowUi['pipelineStepStatus'] ?? [];
            $availableNextSteps = $workflowUi['availableNextSteps'] ?? [];
        @endphp

        @if (!$authorRegistered)
            <div class="alert alert-warning border-0 shadow-sm py-2 mb-3">
                <small><i class="fas fa-exclamation-triangle mr-2"></i>Penulis tidak memiliki akun. ACC Penulis akan
                    dilakukan manual oleh Admin.</small>
            </div>
        @endif

        {{-- FULL-WIDTH PIPELINE --}}
        <div class="pipeline-wrapper py-2">
            <ol class="pipeline-modern mb-0">
                @foreach ($steps as $step)
                    @php
                        $stepStatus = $pipelineStepStatus[$step] ?? 'pending';
                        $isDone = $stepStatus === 'completed' || $stepStatus === 'completed_with_approval';
                        $isCurrent = $stepStatus === 'in_progress';
                        $isApproved = $stepStatus === 'completed_with_approval';
                    @endphp
                    <li
                        class="pipeline-step {{ $isDone ? 'pipeline-step--done pipeline-step--line-done' : '' }} {{ $isCurrent ? 'pipeline-step--current' : '' }} {{ $isApproved ? 'pipeline-step--approved' : '' }}">
                        <div class="pipeline-dot">
                            @if ($isApproved)
                                <i class="fas fa-check-double text-white pipeline-icon"></i>
                            @elseif ($isDone)
                                <i class="fas fa-check text-white pipeline-icon"></i>
                            @elseif ($isCurrent)
                                <i class="fas fa-play text-white pipeline-icon"></i>
                            @else
                                <i class="fas fa-circle text-white" style="font-size: 6px;"></i>
                            @endif
                        </div>
                        <span class="pipeline-label">{{ str_replace('_', ' ', $step) }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- STATUS + ACTION --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-3 border-top">
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div>
                    <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1;">TAHAP AKTIF</small>
                    <strong class="text-primary"
                        style="font-size: 0.85rem;">{{ strtoupper(str_replace('_', ' ', $book->workflow_status)) }}</strong>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <small class="text-muted" style="font-size: 0.6rem;"><i class="fas fa-circle text-success"
                            style="font-size: 0.4rem;"></i> Selesai</small>
                    <small class="text-muted" style="font-size: 0.6rem;"><i class="fas fa-circle text-primary"
                            style="font-size: 0.4rem;"></i> Aktif</small>
                    <small class="text-muted" style="font-size: 0.6rem;"><i class="fas fa-circle text-secondary"
                            style="font-size: 0.4rem;"></i> Menunggu</small>
                    <small class="text-muted" style="font-size: 0.6rem;"><i class="fas fa-check-double text-success"
                            style="font-size: 0.5rem;"></i> ACC</small>
                </div>
            </div>

            <div class="d-flex align-items-center mt-2 mt-md-0" style="gap: 8px;">
                @if (!empty($availableNextSteps) && !$isFinished)
                    <form method="POST" action="{{ route('books.next-workflow', $book) }}"
                        class="d-flex align-items-center" style="gap: 6px;">
                        @csrf
                        <select name="next_step" class="form-control form-control-sm border-0 bg-light"
                            style="font-size: 0.75rem; min-width: 160px;" required>
                            <option value="">Pilih tahap selanjutnya...</option>
                            @foreach ($availableNextSteps as $step)
                                @php
                                    $label = strtoupper(str_replace('_', ' ', $step));
                                    if ($step === 'acc_penulis') {
                                        $label .= !$authorRegistered ? ' (Author/Admin)' : ' (Author)';
                                    } elseif (
                                        in_array($step, [
                                            'audit_isbn',
                                            'ready_for_isbn',
                                            'isbn_submitted',
                                            'isbn_approved',
                                        ])
                                    ) {
                                        $label .= ' (ISBN/Admin)';
                                    } elseif (in_array($step, ['editing', 'editing_review'])) {
                                        $label .= ' (Editor)';
                                    } elseif (in_array($step, ['layout', 'layout_review'])) {
                                        $label .= ' (Layouter)';
                                    } elseif (in_array($step, ['cover_design', 'cover_review'])) {
                                        $label .= ' (Designer)';
                                    }
                                @endphp
                                <option value="{{ $step }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm" @disabled(!$canAdvance)>
                            <i class="fas fa-forward mr-1"></i> Lanjut
                        </button>
                    </form>
                @elseif ($isFinished)
                    <span class="text-success" style="font-size: 0.85rem;"><i class="fas fa-check-circle mr-1"></i>
                        Selesai</span>
                @else
                    <form method="POST" action="{{ route('books.next-workflow', $book) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm" @disabled(!$canAdvance)>
                            <i class="fas fa-forward mr-1"></i> Lanjut
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @unless ($dpPaid)
            <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0 py-2">
                <small><i class="fas fa-exclamation-triangle mr-2"></i>{{ $dpWarning }}</small>
            </div>
        @endunless
    </div>
</div>

<style>
    .pipeline-modern {
        display: flex;
        align-items: flex-start;
        list-style: none;
        padding: 0;
        margin: 0;
        width: 100%;
    }

    .pipeline-step {
        position: relative;
        flex: 1 1 0;
        min-width: 0;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .pipeline-step:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 14px;
        left: 50%;
        right: -50%;
        height: 2px;
        background: #d1d5db;
        z-index: 0;
    }

    .pipeline-step--line-done:not(:last-child)::after {
        background: #28a745;
    }

    .pipeline-dot {
        position: relative;
        z-index: 1;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #d1d5db;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .pipeline-step--done .pipeline-dot {
        background: #28a745;
    }

    .pipeline-step--current .pipeline-dot {
        background: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
    }

    .pipeline-step--approved .pipeline-dot {
        box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.2);
    }

    .pipeline-icon {
        font-size: 11px;
    }

    .pipeline-step--current .pipeline-icon {
        font-size: 10px;
        margin-left: 1px;
    }

    .pipeline-label {
        margin-top: 4px;
        font-size: 0.62rem;
        line-height: 1.2;
        color: #6c757d;
        white-space: normal;
        overflow-wrap: anywhere;
        max-width: 100%;
    }

    .pipeline-step--done .pipeline-label {
        color: #28a745;
        font-weight: 600;
    }

    .pipeline-step--current .pipeline-label {
        color: #007bff;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .pipeline-wrapper {
            overflow-x: auto;
        }

        .pipeline-modern {
            min-width: 820px;
        }
    }
</style>
