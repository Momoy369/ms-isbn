<?php

namespace App\Console\Commands;

use App\Models\AuthorInvoice;
use App\Models\AuthorBookOrder;
use App\Models\BookAssignment;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSystemReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send reminders for overdue staff tasks and pending invoices';

    public function handle(NotificationService $notifications): int
    {
        $workspaceSlaDays = 2;

        $overdueAssignments = BookAssignment::with('book')
            ->whereNull('completed_at')
            ->where('deadline_at', '<', now())
            ->get();

        foreach ($overdueAssignments as $assignment) {
            if ($assignment->user_id) {
                $notifications->send(
                    $assignment->user_id,
                    'Reminder Pekerjaan Terlambat',
                    'Tugas ' . $assignment->role . ' untuk buku ' . optional($assignment->book)->judul . ' sudah melewati deadline.',
                    $assignment->book_id
                );
            }
        }

        $stalePrintOrders = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'reprint')
            ->whereIn('status', ['paid', 'revision_requested', 'printing', 'processing'])
            ->where('updated_at', '<=', now()->subDays($workspaceSlaDays))
            ->get();

        foreach ($stalePrintOrders as $order) {
            if ($order->book) {
                $notifications->sendToBookRoles(
                    $order->book,
                    ['layouter', 'designer', 'editor', 'admin', 'owner', 'finance', 'superadmin'],
                    'Reminder Workspace Percetakan',
                    'Order cetak ulang #' . $order->id . ' untuk buku "' . $order->book->judul . '" sudah ' . $workspaceSlaDays . ' hari tidak bergerak dari status ' . $order->status . '.',
                    $order->user_id
                );
            }

            if ($order->user_id) {
                $notifications->send(
                    $order->user_id,
                    'Update Order Cetak',
                    'Order cetak ulang #' . $order->id . ' masih berada di status ' . $order->status . '. Tim sedang menindaklanjuti.',
                    $order->book_id
                );
            }
        }

        $staleEbookOrders = AuthorBookOrder::with(['book', 'user'])
            ->where('order_type', 'ebook_publication')
            ->whereIn('status', ['paid', 'ebook_revision_requested', 'ebook_publishing'])
            ->where('updated_at', '<=', now()->subDays($workspaceSlaDays))
            ->get();

        foreach ($staleEbookOrders as $order) {
            if ($order->book) {
                $notifications->sendToBookRoles(
                    $order->book,
                    ['layouter', 'designer', 'editor', 'admin', 'owner', 'finance', 'superadmin'],
                    'Reminder Ebook Publishing',
                    'Order ebook publishing #' . $order->id . ' untuk buku "' . $order->book->judul . '" sudah ' . $workspaceSlaDays . ' hari tidak bergerak dari status ' . $order->status . '.',
                    $order->user_id
                );
            }

            if ($order->user_id) {
                $notifications->send(
                    $order->user_id,
                    'Update Ebook Publishing',
                    'Order ebook publishing #' . $order->id . ' masih berada di status ' . $order->status . '. Tim sedang menindaklanjuti.',
                    $order->book_id
                );
            }
        }

        $pendingInvoices = AuthorInvoice::where('status', 'pending')
            ->whereDate('created_at', '<=', now()->subDays(2))
            ->get();

        foreach ($pendingInvoices as $invoice) {
            $notifications->send(
                $invoice->user_id,
                'Reminder Pembayaran Invoice',
                'Invoice ' . $invoice->invoice_number . ' masih pending. Mohon segera lakukan pembayaran.',
                $invoice->book_id
            );
        }

        $this->info('Reminder web-app berhasil dikirim: ' . $overdueAssignments->count() . ' tugas, ' . $stalePrintOrders->count() . ' print order, ' . $staleEbookOrders->count() . ' ebook order, ' . $pendingInvoices->count() . ' invoice.');

        return self::SUCCESS;
    }
}
