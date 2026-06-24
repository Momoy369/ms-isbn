@php($packageItems = $book->packageItems()->get())

@if ($packageItems->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="fas fa-cubes mr-2 text-purple"></i> Item Paket Penerbitan</h5>
        </div>
        <div class="card-body p-3">
            <form action="{{ route('books.package-items.sync', $book) }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync mr-1"></i> Sinkronkan item paket
                </button>
            </form>

            <ul class="list-group list-group-flush">
                @foreach ($packageItems as $item)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="font-weight-bold {{ $item->is_completed ? 'text-success' : 'text-dark' }}">
                                    {{ $item->name }}
                                    @if ($item->is_required)
                                        <span class="badge badge-danger ml-2">Wajib</span>
                                    @endif
                                </div>
                                @if ($item->assigned_to_role)
                                    <div class="small text-muted">Tugas untuk: {{ ucfirst($item->assigned_to_role) }}
                                    </div>
                                @endif
                            </div>
                            <form action="{{ route('book-package-items.toggle', $item) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm {{ $item->is_completed ? 'btn-success' : 'btn-outline-success' }}">
                                    <i class="fas {{ $item->is_completed ? 'fa-check' : 'fa-minus' }} mr-1"></i>
                                    {{ $item->is_completed ? 'Selesai' : 'Tandai Selesai' }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
