<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\SystemSettingAudit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSettingService
{
    private const CACHE_KEY = 'system_settings.map';

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 600, function (): array {
            $rows = SystemSetting::query()
                ->orderBy('key')
                ->get(['key', 'value', 'is_encrypted']);

            $out = [];

            foreach ($rows as $row) {
                $out[(string) $row->key] = $this->decryptIfNeeded((string) $row->value, (bool) $row->is_encrypted);
            }

            return $out;
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public function has(string $key): bool
    {
        $all = $this->all();

        return array_key_exists($key, $all);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, null);

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function setMany(array $values, ?int $actorUserId = null): void
    {
        foreach ($values as $key => $rawValue) {
            $existing = SystemSetting::query()->where('key', $key)->first();
            $oldPlainValue = '';

            if ($existing !== null) {
                $oldPlainValue = $this->decryptIfNeeded((string) $existing->value, (bool) $existing->is_encrypted);
            }

            if ($rawValue === null) {
                SystemSetting::query()->where('key', $key)->delete();
                $this->auditChange($key, $oldPlainValue, null, $actorUserId);
                continue;
            }

            $value = (string) $rawValue;
            $isEncrypted = $this->isSensitiveKey($key) && $value !== '';

            if ($value === '') {
                SystemSetting::query()->where('key', $key)->delete();
                $this->auditChange($key, $oldPlainValue, null, $actorUserId);
                continue;
            }

            if ($isEncrypted) {
                $value = Crypt::encryptString($value);
            }

            SystemSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'is_encrypted' => $isEncrypted,
                ]
            );

            $this->auditChange($key, $oldPlainValue, (string) $rawValue, $actorUserId);
        }

        Cache::forget(self::CACHE_KEY);
    }

    private function auditChange(string $key, ?string $oldValue, ?string $newValue, ?int $actorUserId): void
    {
        $isSensitive = $this->isSensitiveKey($key);
        $oldSafe = $oldValue;
        $newSafe = $newValue;

        if ($isSensitive) {
            $oldSafe = $oldValue === null || $oldValue === '' ? null : '***masked***';
            $newSafe = $newValue === null || $newValue === '' ? null : '***masked***';
        }

        if ($oldSafe === $newSafe) {
            return;
        }

        SystemSettingAudit::query()->create([
            'key' => $key,
            'old_value' => $oldSafe,
            'new_value' => $newSafe,
            'changed_by' => $actorUserId,
            'is_sensitive' => $isSensitive,
        ]);
    }

    public function maskSecret(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $tail = substr($value, -4);

        return '***' . ($tail !== false ? $tail : '');
    }

    private function isSensitiveKey(string $key): bool
    {
        return in_array($key, [
            'license.code',
            'license.issuer_secret',
            'license.revoked_hashes',
            'assistant.openrouter_api_key',
            'assistant.openai_api_key',
            'payment.ipaymu_api_key',
            'integrations.perpusnas_token',
            'integrations.perpusnas_password',
            'reminder.whatsapp_webhook_url',
            'reminder.sms_webhook_url',
        ], true);
    }

    private function decryptIfNeeded(string $value, bool $isEncrypted): string
    {
        if (!$isEncrypted || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
