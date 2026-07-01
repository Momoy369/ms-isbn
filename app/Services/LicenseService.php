<?php

namespace App\Services;

use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;

class LicenseService
{
    public function expectedOwnerLicenseCode(): string
    {
        $owner = User::query()
            ->where('role', 'owner')
            ->orderBy('id')
            ->first();

        $ownerName = $owner?->name ?? 'owner';
        $ownerEmail = $owner?->email ?? 'owner@example.com';
        $appUrl = (string) config('app.url', 'http://localhost');
        $fingerprint = strtolower(trim($ownerEmail)) . '|' . strtolower(trim($ownerName)) . '|' . strtolower(trim($appUrl));

        $hash = strtoupper(substr(hash('sha256', $fingerprint), 0, 24));

        return 'MSI-' . substr($hash, 0, 6) . '-' . substr($hash, 6, 6) . '-' . substr($hash, 12, 6) . '-' . substr($hash, 18, 6);
    }

    public function isLicenseValid(?string $storedCode): bool
    {
        $storedCode = trim((string) $storedCode);

        if ($storedCode === '') {
            return false;
        }

        if ($this->isCommercialToken($storedCode)) {
            $check = $this->validateCommercialToken($storedCode);

            return $check['valid'] === true;
        }

        return hash_equals($this->expectedOwnerLicenseCode(), $storedCode);
    }

    /**
     * @return array{valid: bool, reason: string, type: string}
     */
    public function validationDetails(?string $storedCode): array
    {
        $storedCode = trim((string) $storedCode);

        if ($storedCode === '') {
            return [
                'valid' => false,
                'reason' => 'Kode lisensi kosong.',
                'type' => 'none',
            ];
        }

        if ($this->isCommercialToken($storedCode)) {
            $check = $this->validateCommercialToken($storedCode);

            return [
                'valid' => $check['valid'],
                'reason' => $check['reason'],
                'type' => 'commercial-token',
            ];
        }

        $valid = hash_equals($this->expectedOwnerLicenseCode(), $storedCode);

        return [
            'valid' => $valid,
            'reason' => $valid ? 'Lisensi owner valid.' : 'Kode lisensi owner tidak cocok.',
            'type' => 'owner-code',
        ];
    }

    public function isCommercialToken(string $token): bool
    {
        return substr_count($token, '.') === 2;
    }

    /**
     * @param array{customer_name: string, customer_email?: string|null, domain: string, expires_at?: string|null, plan?: string|null, trial?: bool|null} $claims
     */
    public function generateCommercialToken(array $claims): string
    {
        $secret = $this->issuerSecret();

        if ($secret === '') {
            throw new \RuntimeException('Issuer secret belum dikonfigurasi. Isi license.issuer_secret dulu.');
        }

        $now = time();
        $domain = $this->normalizeDomain((string) ($claims['domain'] ?? ''));

        if ($domain === '') {
            throw new \RuntimeException('Domain lisensi wajib diisi.');
        }

        $expiresAt = null;
        if (!empty($claims['expires_at'])) {
            $dt = new DateTimeImmutable((string) $claims['expires_at'], new DateTimeZone('UTC'));
            $expiresAt = $dt->setTime(23, 59, 59)->getTimestamp();
        }

        $payload = [
            'typ' => 'MSISBN-LIC',
            'sub' => (string) ($claims['customer_name'] ?? 'Unknown Customer'),
            'email' => (string) ($claims['customer_email'] ?? ''),
            'dom' => $domain,
            'plan' => (string) ($claims['plan'] ?? 'standard'),
            'trial' => (bool) ($claims['trial'] ?? false),
            'iat' => $now,
            'exp' => $expiresAt,
        ];

        $headerJson = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if ($headerJson === false || $payloadJson === false) {
            throw new \RuntimeException('Gagal membangun token lisensi.');
        }

        $encodedHeader = $this->base64UrlEncode($headerJson);
        $encodedPayload = $this->base64UrlEncode($payloadJson);
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }

    public function tokenHash(string $token): string
    {
        return hash('sha256', trim($token));
    }

    /**
     * @return array{valid: bool, reason: string}
     */
    public function validateCommercialToken(string $token): array
    {
        $token = trim($token);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return ['valid' => false, 'reason' => 'Format token lisensi tidak valid.'];
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $secret = $this->issuerSecret();

        if ($secret === '') {
            return ['valid' => false, 'reason' => 'Issuer secret belum dikonfigurasi.'];
        }

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true)
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return ['valid' => false, 'reason' => 'Signature token lisensi tidak valid.'];
        }

        $payloadRaw = $this->base64UrlDecode($encodedPayload);
        if ($payloadRaw === '') {
            return ['valid' => false, 'reason' => 'Payload lisensi tidak dapat dibaca.'];
        }

        $payload = json_decode($payloadRaw, true);

        if (!is_array($payload)) {
            return ['valid' => false, 'reason' => 'Payload lisensi bukan JSON valid.'];
        }

        $requiredType = (string) ($payload['typ'] ?? '');
        if ($requiredType !== 'MSISBN-LIC') {
            return ['valid' => false, 'reason' => 'Tipe lisensi tidak dikenali.'];
        }

        $allowedDomain = $this->normalizeDomain((string) ($payload['dom'] ?? ''));
        $currentDomain = $this->currentDomain();

        if ($allowedDomain === '' || $currentDomain === '') {
            return ['valid' => false, 'reason' => 'Domain lisensi atau domain aplikasi tidak valid.'];
        }

        if ($allowedDomain !== $currentDomain) {
            return ['valid' => false, 'reason' => 'Lisensi tidak berlaku untuk domain ini.'];
        }

        $exp = $payload['exp'] ?? null;
        if ($exp !== null && is_numeric($exp) && (int) $exp < time()) {
            return ['valid' => false, 'reason' => 'Lisensi sudah kedaluwarsa.'];
        }

        if ($this->isTokenRevoked($token)) {
            return ['valid' => false, 'reason' => 'Lisensi sudah direvoke.'];
        }

        return ['valid' => true, 'reason' => 'Lisensi komersial valid.'];
    }

    public function isTokenRevoked(string $token): bool
    {
        $hash = $this->tokenHash($token);
        $json = (string) $this->settings()->get('license.revoked_hashes', '[]');
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return false;
        }

        return in_array($hash, $decoded, true);
    }

    public function currentDomain(): string
    {
        $appUrl = (string) config('app.url', '');
        $host = (string) parse_url($appUrl, PHP_URL_HOST);

        return $this->normalizeDomain($host);
    }

    private function settings(): SystemSettingService
    {
        /** @var SystemSettingService $settings */
        $settings = app(SystemSettingService::class);

        return $settings;
    }

    private function issuerSecret(): string
    {
        $fromSettings = trim((string) $this->settings()->get('license.issuer_secret', ''));

        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return trim((string) config('app.license_issuer_secret', env('LICENSE_ISSUER_SECRET', '')));
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim(strtolower($domain));

        if ($domain === '') {
            return '';
        }

        if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
            $parsed = (string) parse_url($domain, PHP_URL_HOST);
            $domain = $parsed !== '' ? $parsed : $domain;
        }

        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        return trim($domain, '/');
    }

    private function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $encoded): string
    {
        $padding = 4 - (strlen($encoded) % 4);
        if ($padding < 4) {
            $encoded .= str_repeat('=', $padding);
        }

        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }
}
