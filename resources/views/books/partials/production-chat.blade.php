<div class="card card-outline card-primary">

    <div class="card-header">

        <h3 class="card-title">

            Diskusi Produksi

        </h3>

    </div>

    <div class="card-body">

        @forelse($book->messages as $message)
            @php

                $badge = 'badge-secondary';

                switch ($message->sender_role) {
                    case 'author':
                        $badge = 'badge-success';
                        break;

                    case 'editor':
                        $badge = 'badge-primary';
                        break;

                    case 'layouter':
                        $badge = 'badge-info';
                        break;

                    case 'designer':
                        $badge = 'badge-warning';
                        break;

                    case 'admin':
                        $badge = 'badge-danger';
                        break;
                }

            @endphp
            <div class="border rounded p-3 mb-2">

                <div class="d-flex align-items-start justify-content-between">

                    <div class="d-flex">

                        <div class="avatar-circle">

                            {{ strtoupper(substr($message->sender_name, 0, 1)) }}

                        </div>

                        <div class="ml-2">

                            <strong>

                                {{ $message->sender_name }}

                            </strong>

                            <span class="badge {{ $badge }} ml-1">

                                {{ strtoupper($message->sender_role) }}

                            </span>

                            <br>

                            <small class="text-muted">

                                {{ $message->created_at->diffForHumans() }}

                            </small>

                        </div>

                    </div>

                </div>

                <hr>

                {{ $message->message }}

                @if ($message->attachment)
                    <hr>

                    <a href="{{ Storage::url($message->attachment) }}" target="_blank"
                        class="btn btn-sm btn-outline-primary">

                        <i class="fas fa-paperclip"></i>

                        Lampiran

                    </a>
                @endif

            </div>

        @empty

            <div class="alert alert-light">

                Belum ada diskusi.

            </div>
        @endforelse

        <form method="POST" enctype="multipart/form-data" action="{{ route('books.message.store', $book) }}">

            @csrf

            <textarea name="message" class="form-control" rows="3" placeholder="Tulis pesan..." required></textarea>

            <input type="file" name="attachment" class="form-control mt-2">

            <button class="btn btn-primary mt-2">

                <i class="fas fa-paper-plane"></i>

                Kirim Pesan

            </button>

        </form>

    </div>

</div>
