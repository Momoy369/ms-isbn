<div class="card card-outline card-info">
    <div class="card-header">
        <h5 class="mb-0">Approval Gate</h5>
    </div>

    <div class="card-body">
        @php
            $approvals = $workflowUi['approvals'] ?? [];
            $approvedCount = (int) ($workflowUi['approvedCount'] ?? 0);
            $totalApprovals = (int) ($workflowUi['totalApprovals'] ?? 0);
        @endphp

        @foreach ($approvals as $approval)
            @php
                $type = (string) ($approval['type'] ?? '-');
                $approved = (bool) ($approval['approved'] ?? false);
                $approvedBy = (string) ($approval['approvedBy'] ?? '');
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div>
                    <strong>{{ strtoupper($type) }}</strong>
                    @if ($approved)
                        <small class="d-block text-muted">oleh {{ $approvedBy }}</small>
                    @else
                        <small class="d-block text-muted">menunggu approval</small>
                    @endif
                </div>
                <span class="badge {{ $approved ? 'badge-success' : 'badge-secondary' }}">
                    {{ $approved ? 'APPROVED' : 'PENDING' }}
                </span>
            </div>
        @endforeach

        <div class="alert {{ $approvedCount === $totalApprovals ? 'alert-success' : 'alert-warning' }} mb-0">
            <strong>{{ $approvedCount }}/{{ $totalApprovals }}</strong> gate approval selesai.
            @if ($approvedCount !== $totalApprovals)
                Tahap ini masih membutuhkan persetujuan yang belum lengkap.
            @endif
        </div>
    </div>
</div>
