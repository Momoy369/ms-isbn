<div class="card">

    <div class="card-header">

        Approval

    </div>

    <div class="card-body">

        @php

            $types = [

                'editor',

                'layout',

                'author'

            ];

        @endphp

        @foreach($types as $type)

            @php

                $approval =
                    $book->approvals
                        ->where(
                            'approval_type',
                            $type
                        )
                        ->first();

            @endphp

            <div class="mb-3">

                <strong>

                    {{ strtoupper($type) }}

                </strong>

                <br>

                @if($approval)

                    <span
                        class="
                            badge
                            badge-success
                        "
                    >

                        APPROVED

                    </span>

                    <br>

                    {{ $approval->approved_by }}

                @else

                    <form
                        method="POST"
                        action="{{ route(
                            'books.approve',
                            [
                                $book,
                                $type
                            ]
                        ) }}"
                    >

                        @csrf

                        <button
                            class="
                                btn
                                btn-sm
                                btn-primary
                            "
                        >

                            Approve

                        </button>

                    </form>

                @endif

            </div>

        @endforeach

    </div>

</div>