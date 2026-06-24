<div class="card">

    <div class="card-header">

        Metadata Buku

    </div>

    <div class="card-body">

        <strong>Penulis</strong>

        <br>

        {{ $book->penulis_1 }}

        <hr>

        <strong>Editor</strong>

        <br>

        {{ $book->editor }}

        <hr>

        <strong>Layouter</strong>

        <br>

        {{ $book->layouter }}

        <hr>

        <strong>Desainer Sampul</strong>

        <br>

        {{ $book->designer }}

        <hr>

        @if ($book->metadata_locked)
            <span class="
                    badge
                    badge-info
                ">

                METADATA LOCKED

            </span>
        @endif

        <hr>

        @if ($book->tanggal_pengajuan_isbn)
            <div class="alert alert-info">

                Tanggal Pengajuan ISBN:

                <strong>

                    {{ $book->tanggal_pengajuan_isbn }}

                </strong>

            </div>
        @endif

        @if ($book->isbn)
            <div class="alert alert-success">

                <strong>
                    ISBN:
                </strong>

                {{ $book->isbn }}

                <br>

                <strong>
                    Tanggal Terbit:
                </strong>

                {{ $book->tanggal_isbn_terbit }}

            </div>
        @endif

        @if ($book->workflow_status === 'isbn_approved')
            <form method="POST" action="{{ route('books.finish', $book) }}">

                @csrf

                <button class="
            btn
            btn-primary
        ">

                    Tandai Selesai

                </button>

            </form>
        @endif

    </div>

</div>
