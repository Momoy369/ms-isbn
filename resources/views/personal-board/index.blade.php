@extends('adminlte::page')

@section('title', 'Papan Pribadi')

@section('css')
    <style>
        .personal-board-page {
            background: radial-gradient(circle at 0% 0%, #f3fff6 0%, #edf7ff 45%, #f7fbff 100%);
            border-radius: 18px;
            padding: 14px;
        }

        .board-shell {
            border: 1px solid #dbe7f3;
            border-radius: 16px;
            overflow: hidden;
        }

        .board-shell .card-header {
            background: linear-gradient(120deg, #0d9488, #0284c7);
            color: #fff;
        }

        .board-shell .card-header small {
            color: rgba(255, 255, 255, 0.88) !important;
        }

        .panel-soft {
            border: 1px solid #dce7f5;
            border-radius: 12px;
            background: #fff;
        }

        .kanban-column {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #d7e4f3;
            background: #fff;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .kanban-column:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        }

        .kanban-dropzone {
            background: linear-gradient(180deg, #f8fbff 0%, #f3f7fc 100%);
            min-height: 380px;
            padding: 10px;
        }

        .kanban-dropzone.drag-over {
            box-shadow: inset 0 0 0 2px #0ea5e9;
            background: #ecfeff;
        }

        .kanban-card {
            border: 1px solid #e3ebf5;
            border-radius: 12px;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            background: #fff;
        }

        .kanban-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            border-color: #c7daee;
        }

        .kanban-card.dragging {
            opacity: .55;
            transform: rotate(1.5deg);
        }

        .kanban-card.drag-origin {
            opacity: .2;
        }

        .kanban-placeholder {
            height: 78px;
            border: 2px dashed #38bdf8;
            border-radius: 12px;
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.12), rgba(14, 165, 233, 0.08));
            margin-bottom: .5rem;
            animation: pulseDrop .9s infinite;
        }

        @keyframes pulseDrop {
            0% {
                opacity: .5;
            }

            50% {
                opacity: .95;
            }

            100% {
                opacity: .5;
            }
        }

        .kanban-card.system-card {
            border-left: 4px solid #06b6d4;
            background: #f6fdff;
        }

        .kanban-card.manual-card {
            border-left: 4px solid #334155;
        }

        .drag-handle {
            cursor: grab;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 11px;
            user-select: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .system-lock {
            font-size: 11px;
            color: #0891b2;
            font-weight: 600;
        }

        .board-toast {
            position: fixed;
            right: 18px;
            bottom: 22px;
            z-index: 1100;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .18s ease, transform .18s ease;
        }

        .board-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .board-toast.success {
            background: #065f46;
            color: #ecfdf5;
        }

        .board-toast.error {
            background: #7f1d1d;
            color: #fef2f2;
        }

        .board-toast.warning {
            background: #78350f;
            color: #fffbeb;
        }

        .dark-mode .personal-board-page {
            background: radial-gradient(circle at 0% 0%, #0f172a 0%, #111827 45%, #020617 100%);
        }

        .dark-mode .board-shell {
            border-color: #1f2937;
            background: #0f172a;
        }

        .dark-mode .panel-soft {
            background: #111827;
            border-color: #1f2937;
            color: #d1d5db;
        }

        .dark-mode .kanban-column {
            background: #111827;
            border-color: #1f2937;
        }

        .dark-mode .kanban-dropzone {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%);
        }

        .dark-mode .kanban-card {
            background: #0f172a;
            border-color: #334155;
            color: #e5e7eb;
        }

        .dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        .dark-mode .form-control,
        .dark-mode .custom-select,
        .dark-mode textarea {
            background: #0f172a;
            border-color: #334155;
            color: #e5e7eb;
        }
    </style>
@endsection

@section('content')
    <div class="personal-board-page">
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

        <div class="card shadow-sm border-0 mb-3 board-shell">
            <div class="card-header">
                <h5 class="mb-0 font-weight-bold">Papan Kerja Otomatis + Manual</h5>
                <small>Kartu sistem dibuat otomatis dari workload role. Kartu manual bisa di-drag dan dikelola
                    bebas.</small>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('personal-board.index') }}" class="panel-soft p-2 mb-3">
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

                <form method="POST" action="{{ route('personal-board.store') }}" class="panel-soft p-2 mb-3">
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
                    <i class="fas fa-hand-paper mr-1"></i>
                    Drag and drop hanya untuk kartu manual. Kartu otomatis tetap read-only dan diarahkan ke modul sumber.
                </div>
            </div>
        </div>

        @php
            $meta = [
                'todo' => ['label' => 'To Do', 'class' => 'primary', 'icon' => 'fas fa-list-ul'],
                'scheduled' => ['label' => 'Scheduled', 'class' => 'warning', 'icon' => 'fas fa-calendar-alt'],
                'done' => ['label' => 'Done', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
            ];

        @endphp

        <div class="row">
            @foreach (['todo', 'scheduled', 'done'] as $column)
                <div class="col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm border-0 kanban-column" data-column="{{ $column }}">
                        <div
                            class="card-header bg-{{ $meta[$column]['class'] }} text-white d-flex justify-content-between align-items-center">
                            <span><i class="{{ $meta[$column]['icon'] }} mr-1"></i> {{ $meta[$column]['label'] }}</span>
                            <span class="badge badge-light">{{ $columns[$column]->count() }}</span>
                        </div>
                        <div class="card-body kanban-dropzone" data-column="{{ $column }}">
                            @forelse ($columns[$column] as $card)
                                @php
                                    $isOverdue = $card->due_at && $card->due_at->isPast() && $column !== 'done';
                                    $priority = strtolower((string) ($card->priority ?? 'medium'));
                                    $priorityClass =
                                        $priority === 'high'
                                            ? 'danger'
                                            : ($priority === 'low'
                                                ? 'secondary'
                                                : 'warning');
                                @endphp
                                <div class="card mb-2 shadow-sm kanban-card {{ !empty($card->is_system) ? 'system-card' : 'manual-card' }}"
                                    data-column="{{ $column }}"
                                    data-card-id="{{ empty($card->is_system) ? (int) $card->id : '' }}"
                                    data-is-manual="{{ empty($card->is_system) ? '1' : '0' }}"
                                    @if (empty($card->is_system)) draggable="true"
                                    data-move-url="{{ route('personal-board.move', $card) }}" @endif>
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong>{{ $card->title }}</strong>
                                            @if ($card->due_at)
                                                <span class="badge badge-{{ $isOverdue ? 'danger' : 'secondary' }}">
                                                    {{ $card->due_at->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        <span
                                            class="badge badge-{{ $priorityClass }} mb-2">{{ strtoupper($priority) }}</span>

                                        @if (!empty($card->content))
                                            <p class="text-muted small mb-2" style="white-space: pre-line;">
                                                {{ $card->content }}</p>
                                        @endif

                                        @if (!empty($card->is_system))
                                            <span class="badge badge-info mb-2">SISTEM</span>
                                            <div class="system-lock mb-2"><i class="fas fa-lock mr-1"></i>Read-only</div>
                                        @else
                                            <span class="badge badge-dark mb-2">MANUAL</span>
                                            <span class="drag-handle mb-2 d-inline-block"
                                                title="Drag dari sini untuk pindah kolom">
                                                <i class="fas fa-grip-vertical mr-1"></i>Drag
                                            </span>
                                        @endif

                                        @if (!empty($card->source_url))
                                            <a href="{{ $card->source_url }}" class="btn btn-xs btn-outline-primary">
                                                <i
                                                    class="fas fa-external-link-alt mr-1"></i>{{ $card->source_label ?? 'Buka Sumber' }}
                                            </a>
                                        @elseif (empty($card->is_system))
                                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                                <button class="btn btn-xs btn-outline-info" type="button"
                                                    data-toggle="collapse"
                                                    data-target="#edit-card-{{ $column }}-{{ $loop->index }}"
                                                    aria-expanded="false">
                                                    Edit
                                                </button>

                                                <form method="POST"
                                                    action="{{ route('personal-board.destroy', $card) }}"
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
                                                            class="form-control form-control-sm"
                                                            value="{{ $card->title }}" required>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <textarea name="content" rows="2" class="form-control form-control-sm">{{ $card->content }}</textarea>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-4 mb-2">
                                                            <select name="board_column"
                                                                class="form-control form-control-sm" required>
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
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = @json(csrf_token());
            const manualCards = Array.from(document.querySelectorAll('.kanban-card[data-is-manual="1"]'));
            const dropzones = Array.from(document.querySelectorAll('.kanban-dropzone'));
            let draggedCard = null;
            let sourceColumn = null;
            let placeholder = null;
            let moveRequestInFlight = false;

            const updateColumnCounts = () => {
                document.querySelectorAll('.kanban-column').forEach((columnNode) => {
                    const manualAndSystemCards = columnNode.querySelectorAll(
                        '.kanban-dropzone > .kanban-card').length;
                    const countBadge = columnNode.querySelector('.card-header .badge');

                    if (countBadge) {
                        countBadge.textContent = String(manualAndSystemCards);
                    }
                });
            };

            const showToast = (message, type = 'success') => {
                const toast = document.createElement('div');
                toast.className = `board-toast ${type}`;
                toast.textContent = message;
                document.body.appendChild(toast);

                requestAnimationFrame(() => toast.classList.add('show'));

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 220);
                }, 1650);
            };

            const clearDropVisuals = () => {
                dropzones.forEach((zone) => zone.classList.remove('drag-over'));
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.removeChild(placeholder);
                }
            };

            const getInsertBeforeManualCard = (zone, pointerY) => {
                const candidates = Array.from(zone.querySelectorAll('.kanban-card.manual-card:not(.dragging)'))
                    .filter((node) => node !== draggedCard);

                return candidates.find((node) => {
                    const rect = node.getBoundingClientRect();
                    return pointerY < rect.top + rect.height / 2;
                }) || null;
            };

            const syncPlaceholderHeight = () => {
                if (!placeholder || !draggedCard) {
                    return;
                }

                placeholder.style.height =
                    `${Math.max(72, Math.round(draggedCard.getBoundingClientRect().height))}px`;
            };

            const placePlaceholder = (zone, pointerY) => {
                if (!placeholder) {
                    return;
                }

                const beforeCard = getInsertBeforeManualCard(zone, pointerY);
                if (beforeCard) {
                    zone.insertBefore(placeholder, beforeCard);
                } else {
                    zone.appendChild(placeholder);
                }
            };

            manualCards.forEach((card) => {
                card.addEventListener('dragstart', (event) => {
                    if (!event.target.closest('.drag-handle')) {
                        event.preventDefault();
                        return;
                    }

                    draggedCard = card;
                    sourceColumn = card.dataset.column;
                    card.classList.add('dragging');
                    card.classList.add('drag-origin');

                    placeholder = document.createElement('div');
                    placeholder.className = 'kanban-placeholder';
                    syncPlaceholderHeight();

                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', card.dataset.moveUrl || '');

                    const ghost = card.cloneNode(true);
                    ghost.style.position = 'fixed';
                    ghost.style.top = '-9999px';
                    ghost.style.width = `${Math.round(card.getBoundingClientRect().width)}px`;
                    ghost.style.pointerEvents = 'none';
                    ghost.style.transform = 'rotate(2deg)';
                    ghost.style.boxShadow = '0 16px 30px rgba(15, 23, 42, 0.25)';
                    document.body.appendChild(ghost);
                    event.dataTransfer.setDragImage(ghost, 24, 16);
                    setTimeout(() => ghost.remove(), 0);
                });

                card.addEventListener('dragend', () => {
                    if (draggedCard) {
                        draggedCard.classList.remove('dragging');
                        draggedCard.classList.remove('drag-origin');
                    }

                    clearDropVisuals();
                    draggedCard = null;
                    sourceColumn = null;
                    placeholder = null;
                });
            });

            dropzones.forEach((zone) => {
                zone.addEventListener('dragover', (event) => {
                    if (!draggedCard) {
                        return;
                    }

                    event.preventDefault();
                    zone.classList.add('drag-over');
                    syncPlaceholderHeight();
                    placePlaceholder(zone, event.clientY);
                });

                zone.addEventListener('dragleave', (event) => {
                    if (!zone.contains(event.relatedTarget)) {
                        zone.classList.remove('drag-over');
                    }
                });

                zone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    zone.classList.remove('drag-over');

                    if (!draggedCard) {
                        return;
                    }

                    const targetColumn = zone.dataset.column;

                    const moveUrl = draggedCard.dataset.moveUrl;
                    if (!moveUrl) {
                        return;
                    }

                    if (!targetColumn) {
                        return;
                    }

                    if (moveRequestInFlight) {
                        showToast('Sedang menyimpan perpindahan sebelumnya. Coba lagi sebentar.',
                            'warning');
                        return;
                    }

                    const fromZone = draggedCard.parentElement;
                    const fromNextSibling = draggedCard.nextElementSibling;
                    const sourceColumnAtDrop = sourceColumn;

                    let beforeCard = null;

                    if (placeholder && placeholder.parentNode === zone) {
                        beforeCard = placeholder.nextElementSibling;
                        if (!beforeCard || beforeCard === draggedCard || beforeCard.dataset
                            .isManual !== '1') {
                            beforeCard = null;
                        }

                        if (beforeCard) {
                            zone.insertBefore(draggedCard, beforeCard);
                        } else {
                            zone.appendChild(draggedCard);
                        }
                    } else {
                        zone.appendChild(draggedCard);
                    }

                    draggedCard.dataset.column = targetColumn;
                    updateColumnCounts();

                    clearDropVisuals();

                    const body = new URLSearchParams({
                        _token: csrfToken,
                        to_column: targetColumn,
                        before_id: beforeCard && beforeCard.dataset.cardId ? beforeCard
                            .dataset.cardId : '',
                    });

                    fetch(moveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json, text/plain, */*'
                            },
                            body: body.toString(),
                        })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error('Move failed');
                            }
                            showToast('Perubahan tersimpan', 'success');
                        })
                        .catch(() => {
                            if (fromZone) {
                                if (fromNextSibling && fromNextSibling.parentElement ===
                                    fromZone) {
                                    fromZone.insertBefore(draggedCard, fromNextSibling);
                                } else {
                                    fromZone.appendChild(draggedCard);
                                }
                                draggedCard.dataset.column = sourceColumn || draggedCard.dataset
                                draggedCard.dataset.column = sourceColumnAtDrop || draggedCard
                                    .dataset.column;
                                updateColumnCounts();
                            }

                            showToast('Gagal menyimpan posisi kartu', 'error');
                        })
                        .finally(() => {
                            moveRequestInFlight = false;
                        });

                    moveRequestInFlight = true;
                });
            });
        });
    </script>
@endsection
