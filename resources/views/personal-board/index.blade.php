@extends('adminlte::page')

@section('title', 'Papan Pribadi')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-pill">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning border-0 shadow-sm rounded-pill">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0 font-weight-bold">Papan Kerja Otomatis Per Role</h5>
            <small class="text-muted">Kartu sistem dibuat otomatis dari workload role. Anda tetap bisa menambah kartu manual
                pribadi.</small>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('personal-board.index') }}" class="border rounded p-2 mb-3 bg-light">
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <label class="small mb-1">Filter Prioritas</label>
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">Semua Prioritas</option>
                            <option value="high" @selected(($filters['priority'] ?? '') === 'high')>High</option>
                            <option value="medium" @selected(($filters['priority'] ?? '') === 'medium')>Medium</option>
                            <option value="low" @selected(($filters['priority'] ?? '') === 'low')>Low</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small mb-1">Filter Due Date</label>
                        <select name="due_filter" class="form-control form-control-sm">
                            <option value="">Semua</option>
                            <option value="overdue" @selected(($filters['due_filter'] ?? '') === 'overdue')>Overdue</option>
                            <option value="today" @selected(($filters['due_filter'] ?? '') === 'today')>Hari Ini</option>
                            <option value="next7" @selected(($filters['due_filter'] ?? '') === 'next7')>7 Hari Ke Depan</option>
                            <option value="no_due" @selected(($filters['due_filter'] ?? '') === 'no_due')>Tanpa Due Date</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button class="btn btn-sm btn-outline-primary btn-block">Terapkan</button>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <a href="{{ route('personal-board.index') }}"
                            class="btn btn-sm btn-outline-secondary btn-block">Reset</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('personal-board.store') }}" class="border rounded p-2 mb-3">
                @csrf
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="small mb-1">Judul Kartu Manual</label>
                        <input type="text" name="title" class="form-control" maxlength="140" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small mb-1">Kolom</label>
                        <select name="board_column" class="form-control" required>
                            <option value="todo">To Do</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small mb-1">Prioritas</label>
                        <select name="priority" class="form-control" required>
                            <option value="high">High</option>
                            <option value="medium" selected>Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small mb-1">Due Date</label>
                        <input type="date" name="due_at" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-1"></i> Tambah
                        </button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small mb-1">Catatan</label>
                    <textarea name="content" rows="2" class="form-control" placeholder="Catatan personal manual..."></textarea>
                </div>
            </form>

            <div class="alert alert-info mb-0">
                <i class="fas fa-robot mr-1"></i>
                Kartu otomatis tidak bisa diubah manual. Untuk mengubahnya, lakukan update dari modul sumbernya.
            </div>
        </div>
    </div>

    @php
        $meta = [
            'todo' => ['label' => 'To Do', 'class' => 'primary', 'icon' => 'fas fa-list-ul'],
            'scheduled' => ['label' => 'Scheduled', 'class' => 'warning', 'icon' => 'fas fa-calendar-alt'],
            'done' => ['label' => 'Done', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
        ];

        $nextMap = ['todo' => 'scheduled', 'scheduled' => 'done', 'done' => 'done'];
        $prevMap = ['todo' => 'todo', 'scheduled' => 'todo', 'done' => 'scheduled'];

    @endphp

    <div class="row">
        @foreach (['todo', 'scheduled', 'done'] as $column)
            <div class="col-lg-4 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <div
                        class="card-header bg-{{ $meta[$column]['class'] }} text-white d-flex justify-content-between align-items-center">
                        <span><i class="{{ $meta[$column]['icon'] }} mr-1"></i> {{ $meta[$column]['label'] }}</span>
                        <span class="badge badge-light">{{ $columns[$column]->count() }}</span>
                    </div>
                    <div class="card-body" style="background:#f8fafc; min-height: 320px;">
                        @forelse ($columns[$column] as $card)
                            @php
                                $isOverdue = $card->due_at && $card->due_at->isPast() && $column !== 'done';
                                $priority = strtolower((string) ($card->priority ?? 'medium'));
                                $priorityClass =
                                    $priority === 'high' ? 'danger' : ($priority === 'low' ? 'secondary' : 'warning');
                            @endphp
                            <div class="card mb-2 border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong>{{ $card->title }}</strong>
                                        @if ($card->due_at)
                                            <span class="badge badge-{{ $isOverdue ? 'danger' : 'secondary' }}">
                                                {{ $card->due_at->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    <span class="badge badge-{{ $priorityClass }} mb-2">{{ strtoupper($priority) }}</span>

                                    @if (!empty($card->content))
                                        <p class="text-muted small mb-2" style="white-space: pre-line;">
                                            {{ $card->content }}</p>
                                    @endif

                                    @if (!empty($card->is_system))
                                        <span class="badge badge-info mb-2">SISTEM</span>
                                    @else
                                        <span class="badge badge-dark mb-2">MANUAL</span>
                                    @endif

                                    @if (!empty($card->source_url))
                                        <a href="{{ $card->source_url }}" class="btn btn-xs btn-outline-primary">
                                            <i
                                                class="fas fa-external-link-alt mr-1"></i>{{ $card->source_label ?? 'Buka Sumber' }}
                                        </a>
                                    @elseif (empty($card->is_system))
                                        <div class="d-flex flex-wrap" style="gap: 6px;">
                                            @if ($column !== 'todo')
                                                <form method="POST" action="{{ route('personal-board.move', $card) }}">
                                                    @csrf
                                                    <input type="hidden" name="to_column"
                                                        value="{{ $prevMap[$column] }}">
                                                    <button class="btn btn-xs btn-outline-secondary">←</button>
                                                </form>
                                            @endif

                                            @if ($column !== 'done')
                                                <form method="POST" action="{{ route('personal-board.move', $card) }}">
                                                    @csrf
                                                    <input type="hidden" name="to_column"
                                                        value="{{ $nextMap[$column] }}">
                                                    <button class="btn btn-xs btn-outline-primary">→</button>
                                                </form>
                                            @endif

                                            <button class="btn btn-xs btn-outline-info" type="button"
                                                data-toggle="collapse"
                                                data-target="#edit-card-{{ $column }}-{{ $loop->index }}"
                                                aria-expanded="false">
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route('personal-board.destroy', $card) }}"
                                                onsubmit="return confirm('Arsipkan kartu manual ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-outline-danger">Arsip</button>
                                            </form>
                                        </div>

                                        <div class="collapse mt-2"
                                            id="edit-card-{{ $column }}-{{ $loop->index }}">
                                            <form method="POST" action="{{ route('personal-board.update', $card) }}"
                                                class="border-top pt-2 mt-2">
                                                @csrf
                                                @method('PUT')
                                                <div class="form-group mb-2">
                                                    <input type="text" name="title"
                                                        class="form-control form-control-sm" value="{{ $card->title }}"
                                                        required>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <textarea name="content" rows="2" class="form-control form-control-sm">{{ $card->content }}</textarea>
                                                </div>
                                                <div class="form-row">
                                                    <div class="col-4 mb-2">
                                                        <select name="board_column" class="form-control form-control-sm"
                                                            required>
                                                            <option value="todo" @selected($card->board_column === 'todo')>To Do
                                                            </option>
                                                            <option value="scheduled" @selected($card->board_column === 'scheduled')>
                                                                Scheduled
                                                            </option>
                                                            <option value="done" @selected($card->board_column === 'done')>Done
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4 mb-2">
                                                        <select name="priority" class="form-control form-control-sm"
                                                            required>
                                                            <option value="high" @selected(($card->priority ?? 'medium') === 'high')>High
                                                            </option>
                                                            <option value="medium" @selected(($card->priority ?? 'medium') === 'medium')>Medium
                                                            </option>
                                                            <option value="low" @selected(($card->priority ?? 'medium') === 'low')>Low
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4 mb-2">
                                                        <input type="date" name="due_at"
                                                            class="form-control form-control-sm"
                                                            value="{{ $card->due_at ? $card->due_at->format('Y-m-d') : '' }}">
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-primary btn-block">Simpan</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada kartu.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
