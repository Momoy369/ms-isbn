<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class EbookPublishingWorkspaceController extends Controller
{
    private const STATUS_OPTIONS = [
        'paid',
        'ebook_revision_requested',
        'ebook_publishing',
        'ebook_published',
        'cancelled',
    ];

    private const ALLOWED_TRANSITIONS = [
        'invoiced' => ['paid', 'cancelled'],
        'paid' => ['ebook_revision_requested', 'ebook_publishing', 'cancelled'],
        'ebook_revision_requested' => ['ebook_publishing', 'cancelled'],
        'ebook_publishing' => ['ebook_revision_requested', 'ebook_published', 'cancelled'],
        'ebook_published' => [],
        'cancelled' => [],
    ];

    public function index(Request $request)
    {
        $query = AuthorBookOrder::with(['book', 'user', 'invoice'])
            ->where('order_type', 'ebook_publication')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function ($inner) use ($keyword) {
                $inner->where('title', 'like', "%{$keyword}%")
                    ->orWhereHas('book', fn($bookQ) => $bookQ->where('judul', 'like', "%{$keyword}%"))
                    ->orWhereHas('user', fn($userQ) => $userQ->where('name', 'like', "%{$keyword}%"));
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        $stats = [
            'invoiced' => AuthorBookOrder::where('order_type', 'ebook_publication')->where('status', 'invoiced')->count(),
            'paid' => AuthorBookOrder::where('order_type', 'ebook_publication')->where('status', 'paid')->count(),
            'revision' => AuthorBookOrder::where('order_type', 'ebook_publication')->where('status', 'ebook_revision_requested')->count(),
            'publishing' => AuthorBookOrder::where('order_type', 'ebook_publication')->where('status', 'ebook_publishing')->count(),
            'published' => AuthorBookOrder::where('order_type', 'ebook_publication')->where('status', 'ebook_published')->count(),
        ];

        return view('ebook.workspace.index', compact('orders', 'stats'));
    }

    public function show(AuthorBookOrder $order)
    {
        if ($order->order_type !== 'ebook_publication') {
            return back()->with('warning', 'Order ini bukan order ebook publishing.');
        }

        $order->load(['book.messages.user', 'book.assignments.user', 'user', 'invoice']);
        $book = $order->book;

        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        return view('ebook.workspace.show', compact('order', 'book'));
    }

    public function updateStatus(Request $request, AuthorBookOrder $order, NotificationService $notifications)
    {
        if ($order->order_type !== 'ebook_publication') {
            return back()->with('warning', 'Order ini bukan order ebook publishing.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'ebook_platform' => ['nullable', 'string', 'max:120'],
            'ebook_publication_link' => ['nullable', 'url', 'max:2000'],
        ]);

        $this->ensureStatusTransitionAllowed((string) $order->status, (string) $data['status']);

        $updates = [
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $order->notes,
            'ebook_platform' => $data['ebook_platform'] ?? $order->ebook_platform,
            'ebook_publication_link' => $data['ebook_publication_link'] ?? $order->ebook_publication_link,
        ];

        if ($data['status'] === 'ebook_publishing' && !$order->ebook_submitted_at) {
            $updates['ebook_submitted_at'] = now();
        }

        if ($data['status'] === 'ebook_published' && !$order->ebook_published_at) {
            $updates['ebook_published_at'] = now();
        }

        $order->update($updates);

        $this->notifyAuthor($order, $notifications, $data['status']);

        return back()->with('success', 'Status ebook publishing berhasil diperbarui.');
    }

    public function requestRevision(Request $request, AuthorBookOrder $order, NotificationService $notifications)
    {
        if ($order->order_type !== 'ebook_publication') {
            return back()->with('warning', 'Order ini bukan order ebook publishing.');
        }

        $book = $order->book;
        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $this->ensureStatusTransitionAllowed((string) $order->status, 'ebook_revision_requested');

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('messages', 'public');
        }

        $book->messages()->create([
            'user_id' => auth()->id(),
            'sender_name' => auth()->user()->name,
            'sender_role' => auth()->user()->role,
            'message' => '[EBOOK_REVISION] ' . $data['message'],
            'attachment' => $path,
        ]);

        $order->update([
            'status' => 'ebook_revision_requested',
            'notes' => $data['message'],
        ]);

        $notifications->sendToBookRoles(
            $book,
            ['layouter', 'designer'],
            'Revisi Ebook Diminta',
            'Tim ebook publishing meminta revisi untuk buku "' . $book->judul . '".',
            auth()->id()
        );

        $this->notifyAuthor($order, $notifications, 'ebook_revision_requested');

        app(\App\Services\BookActivityService::class)->log(
            $book,
            'Revisi Ebook Publishing',
            auth()->user()->name . ' meminta revisi untuk publikasi ebook.'
        );

        return back()->with('success', 'Permintaan revisi ebook berhasil dikirim.');
    }

    private function notifyAuthor(AuthorBookOrder $order, NotificationService $notifications, string $status): void
    {
        if (!$order->user_id) {
            return;
        }

        $messageMap = [
            'ebook_revision_requested' => 'Naskah ebook Anda membutuhkan revisi sebelum dipublikasikan.',
            'ebook_publishing' => 'Naskah ebook Anda sedang dipublikasikan ke platform.',
            'ebook_published' => 'Ebook Anda sudah dipublikasikan' . ($order->ebook_publication_link ? ' dan dapat diakses pada tautan yang disediakan.' : '.'),
        ];

        if (!isset($messageMap[$status])) {
            return;
        }

        $notifications->send(
            $order->user_id,
            'Update Ebook Publishing',
            $messageMap[$status],
            $order->book_id
        );
    }

    private function ensureStatusTransitionAllowed(string $current, string $next): void
    {
        if ($current === $next) {
            return;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$current] ?? [];
        if (!in_array($next, $allowed, true)) {
            abort(422, 'Transisi status tidak valid dari ' . strtoupper($current) . ' ke ' . strtoupper($next) . '.');
        }
    }
}
