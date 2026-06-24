@extends('adminlte::page')

@section('title', 'Notification Center')

@section('content')

    <div class="card">

        <div class="card-header">

            Notification Center

        </div>

        <div class="card-body">

            @forelse($notifications as $notification)
                <div class="border rounded p-3 mb-3">

                    <strong>

                        {{ $notification->title }}

                    </strong>

                    @if (!$notification->is_read)
                        <span class="badge badge-danger">

                            BARU

                        </span>
                    @endif

                    <br>

                    <small class="text-muted">

                        {{ $notification->created_at->format('d M Y H:i') }}

                    </small>

                    <hr>

                    {{ $notification->message }}

                    @if ($notification->book)
                        <hr>

                        <a href="{{ route('books.show', $notification->book) }}" class="btn btn-primary btn-sm">

                            Buka Buku

                        </a>
                    @endif

                    @if (!$notification->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="mt-2">

                            @csrf

                            <button class="btn btn-success btn-sm">

                                Tandai Dibaca

                            </button>

                        </form>
                    @endif

                </div>

            @empty

                <div class="alert alert-info">

                    Belum ada notifikasi.

                </div>
            @endforelse

            {{ $notifications->links() }}

        </div>

    @endsection
