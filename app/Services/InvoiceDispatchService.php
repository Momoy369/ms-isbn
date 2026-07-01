<?php

namespace App\Services;

use App\Models\AuthorInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceDispatchService
{
    public function dispatchAuthorInvoice(AuthorInvoice $invoice): void
    {
        $invoice->loadMissing(['book', 'user']);

        $user = $invoice->user;
        if (!$user) {
            return;
        }

        $title = 'Invoice Baru Diterbitkan';
        $message = sprintf(
            'Invoice %s untuk buku "%s" sebesar Rp %s telah diterbitkan.',
            $invoice->invoice_number,
            $invoice->book->judul ?? '-',
            number_format((float) $invoice->amount, 0, ',', '.')
        );

        app(NotificationService::class)->send(
            $user->id,
            $title,
            $message,
            $invoice->book_id
        );

        $this->sendEmail($user->email, $title, $message);
        $this->sendWhatsapp($user->phone, $message);
        $this->sendSms($user->phone, $message);
    }

    private function sendEmail(?string $email, string $title, string $message): void
    {
        $settings = app(SystemSettingService::class);

        if (!$email || !$settings->getBool('reminder.email_enabled', (bool) config('services.reminder.email_enabled', false))) {
            return;
        }

        try {
            Mail::raw($message, function ($mail) use ($email, $title) {
                $mail->to($email)->subject($title);
            });
        } catch (\Throwable $e) {
            Log::warning('Email invoice dispatch gagal.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWhatsapp(?string $phone, string $message): void
    {
        $settings = app(SystemSettingService::class);

        if (!$phone || !$settings->getBool('reminder.whatsapp_enabled', (bool) config('services.reminder.whatsapp_enabled', false))) {
            return;
        }

        $url = (string) $settings->get('reminder.whatsapp_webhook_url', config('services.reminder.whatsapp_webhook_url', ''));
        if ($url === '') {
            Log::info('WhatsApp dispatch dilewati, webhook belum disetel.', ['phone' => $phone]);
            return;
        }

        try {
            Http::timeout(15)->post($url, [
                'phone' => $phone,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp invoice dispatch gagal.', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendSms(?string $phone, string $message): void
    {
        $settings = app(SystemSettingService::class);

        if (!$phone || !$settings->getBool('reminder.sms_enabled', (bool) config('services.reminder.sms_enabled', false))) {
            return;
        }

        $url = (string) $settings->get('reminder.sms_webhook_url', config('services.reminder.sms_webhook_url', ''));
        if ($url === '') {
            Log::info('SMS dispatch dilewati, webhook belum disetel.', ['phone' => $phone]);
            return;
        }

        try {
            Http::timeout(15)->post($url, [
                'phone' => $phone,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SMS invoice dispatch gagal.', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
