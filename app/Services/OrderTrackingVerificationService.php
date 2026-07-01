<?php

namespace App\Services;

use App\Models\StoreOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderTrackingVerificationService
{
    public function isEnabled(): bool
    {
        $settings = app(SystemSettingService::class);

        return $settings->getBool('tracking.verification_enabled', false);
    }

    /**
     * @return array{ok:bool,message:string,challenge_id:?string,expires_in_minutes:int}
     */
    public function startChallenge(StoreOrder $order, string $channel, string $contact): array
    {
        $settings = app(SystemSettingService::class);

        if (!$this->isEnabled()) {
            return [
                'ok' => false,
                'message' => 'Verifikasi tracking belum diaktifkan.',
                'challenge_id' => null,
                'expires_in_minutes' => 0,
            ];
        }

        $allowed = $this->allowedChannels();
        if (!in_array($channel, $allowed, true)) {
            return [
                'ok' => false,
                'message' => 'Channel verifikasi tidak diizinkan.',
                'challenge_id' => null,
                'expires_in_minutes' => 0,
            ];
        }

        if (!$this->contactMatchesOrder($order, $channel, $contact)) {
            return [
                'ok' => false,
                'message' => 'Data verifikasi tidak cocok dengan data order.',
                'challenge_id' => null,
                'expires_in_minutes' => 0,
            ];
        }

        $otp = (string) random_int(100000, 999999);
        $expiresIn = (int) $settings->get('tracking.otp_expiration_minutes', 10);
        if ($expiresIn < 1) {
            $expiresIn = 10;
        }

        $challengeId = 'trk-' . $order->id . '-' . now()->timestamp . '-' . random_int(100, 999);
        $cacheKey = $this->challengeCacheKey($challengeId);

        Cache::put($cacheKey, [
            'order_id' => (int) $order->id,
            'order_number' => (string) $order->order_number,
            'channel' => $channel,
            'contact' => $contact,
            'otp' => $otp,
            'attempts' => 0,
        ], now()->addMinutes($expiresIn));

        $sent = $this->sendOtp($channel, $contact, $otp, $order->order_number);

        if (!$sent['ok']) {
            Cache::forget($cacheKey);

            return [
                'ok' => false,
                'message' => $sent['message'],
                'challenge_id' => null,
                'expires_in_minutes' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Kode OTP sudah dikirim ke channel verifikasi Anda.',
            'challenge_id' => $challengeId,
            'expires_in_minutes' => $expiresIn,
        ];
    }

    /**
     * @return array{ok:bool,message:string,order_number:?string}
     */
    public function verifyChallenge(string $challengeId, string $otp): array
    {
        $cacheKey = $this->challengeCacheKey($challengeId);
        $payload = Cache::get($cacheKey);

        if (!is_array($payload)) {
            return [
                'ok' => false,
                'message' => 'Kode verifikasi tidak ditemukan atau sudah kedaluwarsa.',
                'order_number' => null,
            ];
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        if ($attempts >= 5) {
            Cache::forget($cacheKey);

            return [
                'ok' => false,
                'message' => 'Melebihi batas percobaan OTP. Silakan ulangi dari awal.',
                'order_number' => null,
            ];
        }

        $inputOtp = trim($otp);
        if ($inputOtp === '' || !hash_equals((string) ($payload['otp'] ?? ''), $inputOtp)) {
            $payload['attempts'] = $attempts + 1;
            Cache::put($cacheKey, $payload, now()->addMinutes(10));

            return [
                'ok' => false,
                'message' => 'Kode OTP tidak valid.',
                'order_number' => null,
            ];
        }

        $orderNumber = (string) ($payload['order_number'] ?? '');
        Cache::forget($cacheKey);

        return [
            'ok' => true,
            'message' => 'Verifikasi berhasil.',
            'order_number' => $orderNumber !== '' ? $orderNumber : null,
        ];
    }

    /**
     * @return array<int,string>
     */
    public function allowedChannels(): array
    {
        $settings = app(SystemSettingService::class);
        $raw = (string) $settings->get('tracking.allowed_channels', 'phone,email,whatsapp');
        $items = array_values(array_filter(array_map('trim', explode(',', strtolower($raw)))));

        $allowed = [];
        foreach ($items as $item) {
            if (in_array($item, ['phone', 'email', 'whatsapp'], true)) {
                $allowed[] = $item;
            }
        }

        if (empty($allowed)) {
            return ['phone'];
        }

        return array_values(array_unique($allowed));
    }

    private function contactMatchesOrder(StoreOrder $order, string $channel, string $contact): bool
    {
        $input = $this->normalizeContact($channel, $contact);

        if ($channel === 'email') {
            $target = $this->normalizeContact('email', (string) ($order->customer_email ?? ''));
            return $input !== '' && $target !== '' && hash_equals($target, $input);
        }

        $target = $this->normalizeContact('phone', (string) ($order->customer_phone ?? ''));

        return $input !== '' && $target !== '' && hash_equals($target, $input);
    }

    private function normalizeContact(string $channel, string $value): string
    {
        $value = trim($value);

        if ($channel === 'email') {
            return strtolower($value);
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /**
     * @return array{ok:bool,message:string}
     */
    private function sendOtp(string $channel, string $contact, string $otp, string $orderNumber): array
    {
        $settings = app(SystemSettingService::class);
        $message = 'Kode OTP tracking order Anda: ' . $otp . ' (order ' . $orderNumber . '). Jangan berikan kode ini kepada siapa pun.';

        try {
            if ($channel === 'email') {
                $enabled = $settings->getBool('reminder.email_enabled', (bool) config('services.reminder.email_enabled', false));
                if (!$enabled) {
                    return ['ok' => false, 'message' => 'Channel email verifikasi belum diaktifkan.'];
                }

                Mail::raw($message, function ($mail) use ($contact) {
                    $mail->to($contact)->subject('OTP Verifikasi Tracking Order');
                });

                return ['ok' => true, 'message' => 'OTP dikirim via email.'];
            }

            if ($channel === 'whatsapp') {
                $enabled = $settings->getBool('reminder.whatsapp_enabled', (bool) config('services.reminder.whatsapp_enabled', false));
                $url = (string) $settings->get('reminder.whatsapp_webhook_url', config('services.reminder.whatsapp_webhook_url', ''));

                if (!$enabled || $url === '') {
                    return ['ok' => false, 'message' => 'Channel WhatsApp verifikasi belum siap.'];
                }

                Http::timeout(15)->post($url, [
                    'phone' => $contact,
                    'message' => $message,
                ]);

                return ['ok' => true, 'message' => 'OTP dikirim via WhatsApp.'];
            }

            $enabled = $settings->getBool('reminder.sms_enabled', (bool) config('services.reminder.sms_enabled', false));
            $url = (string) $settings->get('reminder.sms_webhook_url', config('services.reminder.sms_webhook_url', ''));

            if (!$enabled || $url === '') {
                return ['ok' => false, 'message' => 'Channel SMS verifikasi belum siap.'];
            }

            Http::timeout(15)->post($url, [
                'phone' => $contact,
                'message' => $message,
            ]);

            return ['ok' => true, 'message' => 'OTP dikirim via SMS/telepon.'];
        } catch (\Throwable $e) {
            Log::warning('Tracking OTP send failed', [
                'channel' => $channel,
                'contact' => $contact,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'Gagal mengirim OTP verifikasi. Silakan coba lagi.'];
        }
    }

    private function challengeCacheKey(string $challengeId): string
    {
        return 'tracking.otp.' . $challengeId;
    }
}
