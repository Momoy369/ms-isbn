<div class="card">

    <div class="card-header">

        Riwayat Assignment

    </div>

    <div class="card-body">

        @forelse($book->assignmentHistories
                ->sortByDesc('created_at')
            as $history)
            <div class="mb-3">

                <strong>

                    {{ strtoupper($history->role) }}

                </strong>

                <br>

                @if ($history->activity === 'assigned')
                    Ditugaskan ke:

                    {{ $history->new_person }}
                @elseif($history->activity === 'reassigned')
                    {{ $history->old_person }}

                    →

                    {{ $history->new_person }}
                @elseif($history->activity === 'completed')
                    Diselesaikan oleh:

                    {{ $history->new_person }}
                @endif

                <br>

                <small class="text-muted">

                    {{ $history->created_at->format('d M Y H:i') }}

                </small>

            </div>

            <hr>

        @empty

            <div class="
                    text-muted
                ">

                Belum ada riwayat assignment

            </div>
        @endforelse

    </div>

</div>
