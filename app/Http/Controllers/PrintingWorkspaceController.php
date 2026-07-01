<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\User;
use App\Services\FinalBookPackageService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PrintingWorkspaceController extends Controller
{
    private const STATUS_OPTIONS = [
        'paid',
        'revision_requested',
        'printing',
        'print_completed',
        'shipping',
        'shipped',
        'delivered',
        'processing',
        'completed',
        'cancelled',
    ];

    private const ALLOWED_TRANSITIONS = [
        'invoiced' => ['paid', 'cancelled'],
        'paid' => ['revision_requested', 'printing', 'processing', 'cancelled'],
        'revision_requested' => ['printing', 'processing', 'cancelled'],
        'printing' => ['revision_requested', 'print_completed', 'completed', 'cancelled'],
        'processing' => ['revision_requested', 'print_completed', 'completed', 'cancelled'],
        'print_completed' => ['shipping', 'shipped', 'delivered', 'completed'],
        'shipping' => ['shipped', 'delivered', 'completed', 'cancelled'],
        'shipped' => ['delivered', 'completed', 'cancelled'],
        'delivered' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function index(Request $request)
    {
        $query = AuthorBookOrder::with(['book', 'user', 'invoice'])
            ->where('order_type', 'reprint')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->integer('book_id'));
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
            'invoiced' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'invoiced')->count(),
            'paid' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'paid')->count(),
            'revision_requested' => AuthorBookOrder::where('order_type', 'reprint')->where('status', 'revision_requested')->count(),
            'printing' => AuthorBookOrder::where('order_type', 'reprint')->whereIn('status', ['printing', 'processing'])->count(),
            'shipping' => AuthorBookOrder::where('order_type', 'reprint')->whereIn('status', ['shipping', 'shipped'])->count(),
            'completed' => AuthorBookOrder::where('order_type', 'reprint')->whereIn('status', ['print_completed', 'delivered', 'completed'])->count(),
        ];

        return view('printing.workspace.index', compact('orders', 'stats'));
    }

    public function updateStatus(Request $request, AuthorBookOrder $order, NotificationService $notifications)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'shipping_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatusTransitionAllowed((string) $order->status, (string) $data['status']);

        $updates = [
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $order->notes,
            'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
            'shipping_notes' => $data['shipping_notes'] ?? $order->shipping_notes,
        ];

        if ($data['status'] === 'revision_requested' && !$order->revision_requested_at) {
            $updates['revision_requested_at'] = now();
        }

        if (in_array($data['status'], ['printing', 'processing'], true) && !$order->print_started_at) {
            $updates['print_started_at'] = now();
        }

        if (in_array($data['status'], ['print_completed', 'completed'], true) && !$order->print_completed_at) {
            $updates['print_completed_at'] = now();
        }

        if ($data['status'] === 'shipping' && !$order->shipping_started_at) {
            $updates['shipping_started_at'] = now();
        }

        if ($data['status'] === 'shipped' && !$order->shipped_at) {
            $updates['shipped_at'] = now();
        }

        if (in_array($data['status'], ['delivered', 'completed'], true) && !$order->delivered_at) {
            $updates['delivered_at'] = now();
        }

        $order->update($updates);

        if (in_array($data['status'], ['print_completed', 'completed'], true)) {
            $this->notifyPrintCompleted($order, $notifications);
            $this->notifyShippingTeam($order, $notifications);
        }

        if (in_array($data['status'], ['shipping', 'shipped', 'delivered'], true)) {
            $this->notifyAuthorShippingProgress($order, $notifications, $data['status']);
        }

        return back()->with('success', 'Status order cetak ulang berhasil diperbarui.');
    }

    public function show(AuthorBookOrder $order, FinalBookPackageService $finalPackage)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $order->load(['book.files', 'book.messages.user', 'book.assignments.user', 'user', 'invoice']);
        $book = $order->book;

        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $checklist = $finalPackage->checklist($book);

        return view('printing.workspace.show', compact('order', 'book', 'checklist'));
    }

    public function requestRevision(Request $request, AuthorBookOrder $order, NotificationService $notifications)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $book = $order->book;
        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $this->ensureStatusTransitionAllowed((string) $order->status, 'revision_requested');

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('messages', 'public');
        }

        $book->messages()->create([
            'user_id' => auth()->id(),
            'sender_name' => auth()->user()->name,
            'sender_role' => auth()->user()->role,
            'message' => '[REQUEST_REVISION] ' . $data['message'],
            'attachment' => $path,
        ]);

        $order->update([
            'status' => 'revision_requested',
            'revision_requested_at' => now(),
            'notes' => $data['message'],
        ]);

        $notifications->sendToBookRoles(
            $book,
            ['layouter', 'designer'],
            'Revisi Diminta Percetakan',
            'Tim percetakan meminta revisi untuk buku "' . $book->judul . '". Mohon ditindak oleh layout/desain.',
            auth()->id()
        );

        app(\App\Services\BookActivityService::class)->log(
            $book,
            'Revisi Percetakan',
            auth()->user()->name . ' meminta revisi ke tim layout/desain.'
        );

        return back()->with('success', 'Permintaan revisi berhasil dikirim ke tim layout/desain.');
    }

    public function uploadFinalFile(Request $request, AuthorBookOrder $order, FinalBookPackageService $finalPackage)
    {
        if ($order->order_type !== 'reprint') {
            return back()->with('warning', 'Order ini bukan order cetak ulang.');
        }

        $book = $order->book;
        if (!$book) {
            return back()->with('warning', 'Buku untuk order ini tidak ditemukan.');
        }

        $data = $request->validate([
            'type' => ['required', 'string'],
            'file' => ['required', 'file', 'max:51200'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $finalPackage->validateAndStore(
                $book,
                $data['type'],
                $request->file('file'),
                $data['note'] ?? null,
                (string) (auth()->user()->role ?? 'printing')
            );
        } catch (\Throwable $e) {
            return back()->with('warning', 'Upload final file gagal: ' . $e->getMessage());
        }

        return back()->with('success', 'Final file berhasil diupload dan diverifikasi.');
    }

    private function notifyPrintCompleted(AuthorBookOrder $order, NotificationService $notifications): void
    {
        if ($order->user_id) {
            $notifications->send(
                $order->user_id,
                'Cetak Buku Selesai',
                'Proses cetak buku "' . ($order->title ?: optional($order->book)->judul ?: '-') . '" telah selesai dan sedang diteruskan ke tim pengiriman.',
                $order->book_id
            );
        }
    }

    private function notifyShippingTeam(AuthorBookOrder $order, NotificationService $notifications): void
    {
        $shippingUsers = User::whereIn('role', ['finance', 'owner', 'admin', 'superadmin'])->get();

        foreach ($shippingUsers as $user) {
            $notifications->send(
                $user->id,
                'Order Siap Dikirim',
                'Order cetak ulang #' . $order->id . ' untuk buku "' . ($order->title ?: optional($order->book)->judul ?: '-') . '" siap diproses pengiriman.',
                $order->book_id
            );
        }
    }

    private function notifyAuthorShippingProgress(AuthorBookOrder $order, NotificationService $notifications, string $status): void
    {
        if (!$order->user_id) {
            return;
        }

        $messageMap = [
            'shipping' => 'Pesanan buku Anda sedang diproses oleh tim pengiriman.',
            'shipped' => 'Pesanan buku Anda sudah dikirim' . ($order->tracking_number ? ' (Resi: ' . $order->tracking_number . ').' : '.'),
            'delivered' => 'Pesanan buku Anda sudah diterima/dinyatakan selesai.',
        ];

        $notifications->send(
            $order->user_id,
            'Update Pengiriman Buku',
            $messageMap[$status] ?? 'Status pengiriman pesanan Anda diperbarui.',
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
