<?php

namespace App\Console\Commands;

use App\Models\AuthorInvoice;
use App\Models\BookAssignment;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSystemReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send reminders for overdue staff tasks and pending invoices';

    public function handle(NotificationService $notifications): int
    {
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

        $this->info('Reminder web-app berhasil dikirim: ' . $overdueAssignments->count() . ' tugas, ' . $pendingInvoices->count() . ' invoice.');

        return self::SUCCESS;
    }
}
