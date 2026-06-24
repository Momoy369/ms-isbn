<div class="card mb-3">

    <div class="card-header">

        ISBN READINESS

    </div>

    <div class="card-body">

        <h2>

            {{ $readiness }}%

        </h2>

        @php

            $color = $readiness >= 90 ? 'bg-success' : ($readiness >= 60 ? 'bg-warning' : 'bg-danger');

        @endphp

        <div class="progress">

            <div class="progress-bar"
                style="
                    width:
                    {{ $readiness }}%;
                ">

                {{ $readiness }}%

            </div>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-4">

        <div class="card bg-success">

            <div class="card-body text-center">

                <h2>
                    {{ $book->audits->where('passed', true)->count() }}
                </h2>

                <strong>PASS</strong>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card bg-danger">

            <div class="card-body text-center">

                <h2>
                    {{ $book->audits->where('passed', false)->count() }}
                </h2>

                <strong>FAIL</strong>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card bg-info">

            <div class="card-body text-center">

                <h2>
                    {{ $book->audits->count() }}
                </h2>

                <strong>TOTAL CHECK</strong>

            </div>

        </div>

    </div>

</div>

<h5 class="mt-3">

    Yang Perlu Diperbaiki

</h5>

<hr>

<ul>

    @foreach ($book->audits->where('passed', false) as $audit)
        <li>

            {{ $audit->rule }}

        </li>
    @endforeach

</ul>
