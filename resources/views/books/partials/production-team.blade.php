<div class="card card-outline card-info">

    <div class="card-header">

        Tim Produksi

    </div>

    <div class="card-body">

        @foreach ($book->assignments as $assignment)
            <div class="
            border
            rounded
            p-2
            mb-2
        ">

                <strong>

                    {{ ucfirst($assignment->role) }}

                </strong>

                <br>

                {{ $assignment->person_name }}

                @php

                    $workload = \App\Models\BookAssignment::where('person_name', $assignment->person_name)
                        ->whereNull('completed_at')
                        ->count();

                @endphp

                <br>

                <span class="badge badge-info">

                    Beban:
                    {{ $workload }}
                    buku

                </span>

                <hr>

                Deadline:

                {{ optional($assignment->deadline_at)->format('d M Y') }}

                <br>

                @php
                    $level = $assignment->getWarningLevel();
                @endphp

                @if ($level == 'completed')
                    <span class="badge badge-success">

                        SELESAI

                    </span>
                @elseif($level == 'overdue')
                    <span class="badge badge-danger">

                        TERLAMBAT

                        {{ $assignment->lateDays() }}

                        hari

                    </span>
                @elseif($level == 'warning')
                    <span class="badge badge-warning">

                        DEADLINE < 24 JAM </span>
                        @else
                            <span class="badge badge-primary">

                                ON TRACK

                            </span>
                @endif

                @if ($assignment->role == 'editor' && $book->workflow_status == 'editing')
                    <form method="POST" action="{{ route('books.files.store', $book) }}" enctype="multipart/form-data"
                        class="mt-2">

                        @csrf

                        <input type="hidden" name="type" value="edited_manuscript">

                        <input type="file" name="file" class="form-control mb-2">

                        <textarea name="note" class="form-control mb-2" rows="3" placeholder="
Pesan untuk penulis...
"></textarea>

                        <button class="btn btn-primary btn-sm">
                            Upload Hasil Editing
                        </button>

                    </form>
                @endif

                @if ($assignment->role == 'layouter' && $book->workflow_status == 'layout')
                    <form method="POST" action="{{ route('books.files.store', $book) }}" enctype="multipart/form-data"
                        class="mt-2">

                        @csrf

                        <input type="hidden" name="type" value="layout_pdf">

                        <input type="file" name="file" class="form-control mb-2">

                        <textarea name="note" class="form-control mb-2" rows="3" placeholder="
Pesan untuk penulis...
"></textarea>

                        <button class="btn btn-info btn-sm">
                            Upload PDF Layout
                        </button>

                    </form>
                @endif

                @if ($assignment->role == 'designer' && $book->workflow_status == 'cover_design')
                    <form method="POST" action="{{ route('books.files.store', $book) }}" enctype="multipart/form-data"
                        class="mt-2">

                        @csrf

                        <input type="hidden" name="type" value="cover_final">

                        <input type="file" name="file" class="form-control mb-2">

                        <textarea name="note" class="form-control mb-2" rows="3" placeholder="
Pesan untuk penulis...
"></textarea>

                        <button class="btn btn-warning btn-sm">
                            Upload Cover Final
                        </button>

                    </form>
                @endif

                @if ($assignment->role == 'editor')
                    @php

                        $lastRevision = $book
                            ->reviews()
                            ->where('stage', 'editing')
                            ->where('status', 'revision')
                            ->latest()
                            ->first();

                    @endphp

                    @if ($lastRevision)
                        <div class="alert alert-warning mt-2">

                            <strong>

                                Revisi Terbaru Penulis

                            </strong>

                            <hr>

                            {{ $lastRevision->note }}

                        </div>
                    @endif

                    @foreach ($book->files->where('type', 'edited_manuscript') as $file)
                        <div>

                            Edited Manuscript

                            <a href="{{ route('files.download', $file) }}">

                                Download

                            </a>

                        </div>
                    @endforeach
                @endif

                @php

                    $lastRevision = $book
                        ->reviews()
                        ->where('stage', 'layout')
                        ->where('status', 'revision')
                        ->latest()
                        ->first();

                @endphp

                @if ($lastRevision)
                    <div class="alert alert-warning mt-2">

                        <strong>

                            Revisi Terbaru Penulis

                        </strong>

                        <hr>

                        {{ $lastRevision->note }}

                    </div>
                @endif

                @php

                    $lastRevision = $book
                        ->reviews()
                        ->where('stage', 'cover')
                        ->where('status', 'revision')
                        ->latest()
                        ->first();

                @endphp

                @if ($lastRevision)
                    <div class="alert alert-warning mt-2">

                        <strong>

                            Revisi Terbaru Penulis

                        </strong>

                        <hr>

                        {{ $lastRevision->note }}

                    </div>
                @endif

                @if (
                    !$assignment->completed_at &&
                        (($assignment->role == 'editor' && $book->workflow_status == 'editing') ||
                            ($assignment->role == 'layouter' && $book->workflow_status == 'layout') ||
                            ($assignment->role == 'designer' && $book->workflow_status == 'cover_design')))
                    <form method="POST" action="{{ route('assignments.complete', $assignment) }}" class="mt-2">

                        @csrf

                        <button class="btn btn-success btn-sm">

                            Tandai Selesai

                        </button>

                    </form>
                @endif


            </div>
        @endforeach

        <form method="POST" action="{{ route('books.auto-assign', $book) }}">

            @csrf

            <button class="
        btn
        btn-primary
        btn-block
        mb-2
    ">

                Auto Assign Tim

            </button>

        </form>

        <form method="POST" action="{{ route('books.sync-assignment', $book) }}">

            @csrf

            <button
                class="
                    btn
                    btn-info
                    btn-block
                ">

                Sinkronisasi Tim

            </button>

        </form>

        <hr>



    </div>

</div>
