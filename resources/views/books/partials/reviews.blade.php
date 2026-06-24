<div class="card card-outline card-warning">

    <div class="card-header">

        Review Penulis

    </div>

    <div class="card-body">

        @forelse($reviews as $review)
            <div class="border rounded p-2 mb-2">

                <strong>

                    {{ strtoupper($review->stage) }}

                </strong>

                <br>

                Status:

                @if ($review->status == 'approved')
                    <span class="badge badge-success">

                        APPROVED

                    </span>
                @else
                    <span class="badge badge-danger">

                        REVISION

                    </span>
                @endif

                <hr>

                {{ $review->note }}

                <br>

                @if ($review->attachment)
                    <a href="{{ Storage::url($review->attachment) }}">

                        Download Lampiran

                    </a>
                @endif

            </div>

        @empty

            <p class="text-muted">

                Belum ada review.

            </p>
        @endforelse

        @if ($reviews->hasPages())
            <div class="mt-3">

                {{ $reviews->links() }}

            </div>
        @endif

    </div>

</div>
