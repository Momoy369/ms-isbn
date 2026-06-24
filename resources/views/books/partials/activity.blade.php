<div class="card card-outline card-secondary">

    <div class="card-header">

        <h3 class="card-title">

            Timeline Aktivitas

        </h3>

    </div>

    <div class="card-body">

        @forelse($activities as $activity)

            <div class="border-left border-info pl-3 mb-3">

                <strong>

                    {{ $activity->activity }}

                </strong>

                <br>

                <small class="text-muted">

                    {{ $activity->created_at->format('d M Y H:i') }}

                </small>

                @if($activity->description)

                    <div class="mt-2">

                        {{ $activity->description }}

                    </div>

                @endif

            </div>

        @empty

            <p class="text-muted">

                Belum ada aktivitas.

            </p>

        @endforelse

        <div class="mt-3">

            {{ $activities->links() }}

        </div>

    </div>

</div>