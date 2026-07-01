<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use App\Models\AuthorUpgradeRequest;
use App\Models\Book;
use App\Models\BookAssignment;
use App\Models\PersonalBoardCard;
use App\Models\StorePackageConsultation;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class PersonalBoardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->startOfDay();

        $priorityFilter = request()->string('priority')->toString();
        $dueFilter = request()->string('due_filter')->toString();

        $systemCards = $this->buildSystemCardsForRole($user);
        $manualCards = PersonalBoardCard::query()
            ->where('user_id', (int) $user->id)
            ->where('is_archived', false)
            ->orderBy('board_column')
            ->orderBy('card_order')
            ->orderByDesc('id')
            ->get()
            ->map(function (PersonalBoardCard $card) {
                $card->is_system = false;
                $card->source_url = null;
                $card->source_label = null;
                return $card;
            });

        $cards = $systemCards->concat($manualCards)->values();

        if (in_array($priorityFilter, ['low', 'medium', 'high'], true)) {
            $cards = $cards->where('priority', $priorityFilter)->values();
        }

        if ($dueFilter === 'overdue') {
            $cards = $cards
                ->filter(fn($card) => !empty($card->due_at) && $card->due_at->lt($today))
                ->values();
        } elseif ($dueFilter === 'today') {
            $cards = $cards
                ->filter(fn($card) => !empty($card->due_at) && $card->due_at->isSameDay($today))
                ->values();
        } elseif ($dueFilter === 'next7') {
            $to = $today->copy()->addDays(7)->endOfDay();
            $cards = $cards
                ->filter(fn($card) => !empty($card->due_at) && $card->due_at->between($today, $to))
                ->values();
        } elseif ($dueFilter === 'no_due') {
            $cards = $cards
                ->filter(fn($card) => empty($card->due_at))
                ->values();
        }

        $grouped = [
            'todo' => $cards->where('board_column', 'todo')->values(),
            'scheduled' => $cards->where('board_column', 'scheduled')->values(),
            'done' => $cards->where('board_column', 'done')->values(),
        ];

        return view('personal-board.index', [
            'columns' => $grouped,
            'filters' => [
                'priority' => $priorityFilter,
                'due_filter' => $dueFilter,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:140',
            'content' => 'nullable|string',
            'board_column' => 'required|in:todo,scheduled,done',
            'priority' => 'required|in:low,medium,high',
            'due_at' => 'nullable|date',
        ]);

        $userId = (int) auth()->id();

        $nextOrder = (int) PersonalBoardCard::query()
            ->where('user_id', $userId)
            ->where('board_column', $data['board_column'])
            ->max('card_order') + 1;

        PersonalBoardCard::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'board_column' => $data['board_column'],
            'priority' => $data['priority'],
            'due_at' => $data['due_at'] ?? null,
            'card_order' => $nextOrder,
        ]);

        return back()->with('success', 'Kartu manual berhasil ditambahkan.');
    }

    public function update(Request $request, PersonalBoardCard $card)
    {
        $this->assertOwner($card);

        $data = $request->validate([
            'title' => 'required|string|max:140',
            'content' => 'nullable|string',
            'board_column' => 'required|in:todo,scheduled,done',
            'priority' => 'required|in:low,medium,high',
            'due_at' => 'nullable|date',
        ]);

        $targetColumn = (string) $data['board_column'];
        $currentColumn = (string) $card->board_column;

        $updates = [
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'board_column' => $targetColumn,
            'priority' => $data['priority'],
            'due_at' => $data['due_at'] ?? null,
        ];

        if ($targetColumn !== $currentColumn) {
            $updates['card_order'] = (int) PersonalBoardCard::query()
                ->where('user_id', (int) auth()->id())
                ->where('board_column', $targetColumn)
                ->max('card_order') + 1;
        }

        $card->update($updates);

        return back()->with('success', 'Kartu manual berhasil diperbarui.');
    }

    public function move(Request $request, PersonalBoardCard $card)
    {
        $this->assertOwner($card);

        $data = $request->validate([
            'to_column' => 'required|in:todo,scheduled,done',
            'before_id' => 'nullable|integer',
        ]);

        $userId = (int) auth()->id();
        $sourceColumn = (string) $card->board_column;
        $targetColumn = (string) $data['to_column'];
        $beforeId = isset($data['before_id']) ? (int) $data['before_id'] : null;

        if ($beforeId === (int) $card->id) {
            $beforeId = null;
        }

        if ($targetColumn !== $sourceColumn) {
            $card->update([
                'board_column' => $targetColumn,
            ]);
        }

        $this->reorderColumnCards($userId, $targetColumn, (int) $card->id, $beforeId);

        if ($sourceColumn !== $targetColumn) {
            $this->normalizeColumnOrder($userId, $sourceColumn);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Kartu manual berhasil dipindahkan.',
            ]);
        }

        return back()->with('success', 'Kartu manual berhasil dipindahkan.');
    }

    public function destroy(PersonalBoardCard $card)
    {
        $this->assertOwner($card);

        $card->update([
            'is_archived' => true,
        ]);

        return back()->with('success', 'Kartu manual dipindahkan ke arsip.');
    }

    private function buildSystemCardsForRole($user): Collection
    {
        $cards = collect();

        $role = (string) $user->role;

        if (in_array($role, ['editor', 'layouter', 'designer', 'admin', 'owner', 'superadmin'], true)) {
            $cards = $cards->concat($this->buildAssignmentCards((int) $user->id));
        }

        if (in_array($role, ['isbn', 'admin', 'owner', 'superadmin'], true)) {
            $cards = $cards->concat($this->buildIsbnQueueCards());
        }

        if (in_array($role, ['finance', 'admin', 'owner', 'superadmin'], true)) {
            $cards = $cards->concat($this->buildFinanceCards());
        }

        if (in_array($role, ['admin', 'isbn', 'superadmin'], true)) {
            $cards = $cards->concat($this->buildUpgradeReviewCards());
        }

        if (in_array($role, ['finance', 'admin', 'owner', 'superadmin'], true)) {
            $cards = $cards->concat($this->buildPackageLeadCards());
        }

        return $cards
            ->sortBy([
                ['board_column', 'asc'],
                ['priority', 'asc'],
            ])
            ->map(function ($card) {
                $card->is_system = true;
                return $card;
            })
            ->values();
    }

    private function buildAssignmentCards(int $userId): Collection
    {
        $assignments = BookAssignment::query()
            ->with('book')
            ->where('user_id', $userId)
            ->latest()
            ->limit(30)
            ->get();

        return $assignments->map(function (BookAssignment $assignment) {
            $dueAt = $assignment->deadline_at ? now()->parse($assignment->deadline_at) : null;
            $isDone = !empty($assignment->completed_at);

            $column = $isDone ? 'done' : 'todo';
            $priority = 'medium';

            if (!$isDone && $dueAt && $dueAt->isPast()) {
                $column = 'scheduled';
                $priority = 'high';
            } elseif (!$isDone && $dueAt && $dueAt->diffInDays(now(), false) <= 2) {
                $column = 'scheduled';
                $priority = 'high';
            } elseif (!$isDone && $dueAt && $dueAt->diffInDays(now(), false) <= 7) {
                $column = 'scheduled';
            } elseif ($isDone) {
                $priority = 'low';
            }

            return (object) [
                'title' => 'Assignment ' . strtoupper((string) $assignment->role) . ' - ' . (string) optional($assignment->book)->judul,
                'content' => 'PIC: ' . (string) $assignment->person_name,
                'board_column' => $column,
                'priority' => $priority,
                'due_at' => $dueAt,
                'source_url' => route('assignments.my'),
                'source_label' => 'Assignment Saya',
            ];
        });
    }

    private function buildIsbnQueueCards(): Collection
    {
        $books = Book::query()
            ->whereIn('workflow_status', ['ready_for_isbn', 'isbn_submitted'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return $books->map(function (Book $book) {
            $isSubmitted = $book->workflow_status === 'isbn_submitted';

            return (object) [
                'title' => ($isSubmitted ? 'Verifikasi ISBN' : 'Submit ISBN') . ' - ' . (string) $book->judul,
                'content' => 'Status: ' . strtoupper(str_replace('_', ' ', (string) $book->workflow_status)),
                'board_column' => $isSubmitted ? 'scheduled' : 'todo',
                'priority' => $isSubmitted ? 'high' : 'medium',
                'due_at' => null,
                'source_url' => route('books.show', $book),
                'source_label' => 'Detail Naskah',
            ];
        });
    }

    private function buildFinanceCards(): Collection
    {
        $invoices = AuthorInvoice::query()
            ->with('book')
            ->where('status', 'pending')
            ->latest()
            ->limit(20)
            ->get();

        return $invoices->map(function (AuthorInvoice $invoice) {
            $dueAt = $invoice->due_date ? now()->parse($invoice->due_date) : null;
            $priority = 'medium';
            $column = 'todo';

            if ($dueAt && $dueAt->isPast()) {
                $priority = 'high';
                $column = 'scheduled';
            }

            return (object) [
                'title' => 'Invoice Pending - ' . (string) $invoice->invoice_number,
                'content' => 'Buku: ' . (string) optional($invoice->book)->judul,
                'board_column' => $column,
                'priority' => $priority,
                'due_at' => $dueAt,
                'source_url' => route('finance.invoices.index'),
                'source_label' => 'Finance Invoice',
            ];
        });
    }

    private function buildUpgradeReviewCards(): Collection
    {
        $requests = AuthorUpgradeRequest::query()
            ->with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(20)
            ->get();

        return $requests->map(function (AuthorUpgradeRequest $request) {
            return (object) [
                'title' => 'Review Upgrade Author - ' . (string) optional($request->user)->name,
                'content' => 'Status: PENDING',
                'board_column' => 'todo',
                'priority' => 'medium',
                'due_at' => null,
                'source_url' => route('admin.author-upgrades.index'),
                'source_label' => 'Review Upgrade',
            ];
        });
    }

    private function buildPackageLeadCards(): Collection
    {
        $leads = StorePackageConsultation::query()
            ->where('status', 'pending')
            ->latest()
            ->limit(20)
            ->get();

        return $leads->map(function (StorePackageConsultation $lead) {
            $dueAt = $lead->next_action_at ? now()->parse($lead->next_action_at) : null;

            return (object) [
                'title' => 'Follow-up Lead Paket - ' . (string) $lead->customer_name,
                'content' => 'Judul Naskah: ' . (string) $lead->manuscript_title,
                'board_column' => $dueAt && $dueAt->isPast() ? 'scheduled' : 'todo',
                'priority' => $dueAt && $dueAt->isPast() ? 'high' : 'medium',
                'due_at' => $dueAt,
                'source_url' => route('finance.store.package-consultations.index'),
                'source_label' => 'Lead Paket',
            ];
        });
    }

    private function assertOwner(PersonalBoardCard $card): void
    {
        if ((int) $card->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function reorderColumnCards(int $userId, string $column, int $movingCardId, ?int $beforeId): void
    {
        $cards = PersonalBoardCard::query()
            ->where('user_id', $userId)
            ->where('is_archived', false)
            ->where('board_column', $column)
            ->orderBy('card_order')
            ->orderBy('id')
            ->get();

        $orderedIds = $cards
            ->pluck('id')
            ->reject(fn($id) => (int) $id === $movingCardId)
            ->values();

        $insertAt = $orderedIds->count();

        if ($beforeId !== null) {
            $idx = $orderedIds->search(fn($id) => (int) $id === $beforeId);
            if ($idx !== false) {
                $insertAt = (int) $idx;
            }
        }

        $orderedIds->splice($insertAt, 0, [$movingCardId]);

        foreach ($orderedIds->values() as $i => $id) {
            PersonalBoardCard::query()
                ->where('id', (int) $id)
                ->where('user_id', $userId)
                ->update([
                    'card_order' => $i + 1,
                ]);
        }
    }

    private function normalizeColumnOrder(int $userId, string $column): void
    {
        $cards = PersonalBoardCard::query()
            ->where('user_id', $userId)
            ->where('is_archived', false)
            ->where('board_column', $column)
            ->orderBy('card_order')
            ->orderBy('id')
            ->get();

        foreach ($cards as $i => $card) {
            $card->update([
                'card_order' => $i + 1,
            ]);
        }
    }

}
