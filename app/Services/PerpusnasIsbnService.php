<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PerpusnasIsbnService
{
    public function verify(string $isbn, ?string $title = null, ?string $year = null): array
    {
        $isbnDigits = $this->normalizeIsbn($isbn);

        if ($isbnDigits === '') {
            return [
                'verified' => false,
                'message' => 'Nomor ISBN kosong atau tidak valid.',
                'source' => 'local',
                'raw' => null,
            ];
        }

        $credentials = $this->credentials();
        if (!$credentials['ready']) {
            return [
                'verified' => false,
                'message' => 'Kredensial API Perpusnas belum lengkap. Isi PERPUSNAS_API_TOKEN/USERNAME/PASSWORD.',
                'source' => 'config',
                'raw' => null,
            ];
        }

        $detail = $this->postJson('/api/isbn/detail', [
            'isbn' => $isbnDigits,
        ]);

        if ($detail['ok']) {
            $matched = $this->matchRows($detail['data'], $isbnDigits, $title);
            if ($matched !== null) {
                return [
                    'verified' => true,
                    'message' => 'ISBN terverifikasi pada endpoint detail ISBN Perpusnas.',
                    'source' => 'detail',
                    'matched' => $matched,
                    'raw' => $detail['raw'],
                ];
            }
        }

        $tagihanPayload = [
            'isbn' => $isbnDigits,
            'length' => 10,
            'page' => 1,
        ];

        if (!empty($title)) {
            $tagihanPayload['title'] = $title;
        }

        if (!empty($year)) {
            $tagihanPayload['tahun_terbit'] = (string) $year;
        }

        $tagihan = $this->postJson('/api/isbn/tagihan', $tagihanPayload);

        if ($tagihan['ok']) {
            $matched = $this->matchRows($tagihan['data'], $isbnDigits, $title);
            if ($matched !== null) {
                return [
                    'verified' => true,
                    'message' => 'ISBN terverifikasi pada endpoint pencarian tagihan ISBN Perpusnas.',
                    'source' => 'tagihan',
                    'matched' => $matched,
                    'raw' => $tagihan['raw'],
                ];
            }
        }

        $errorMessage = $detail['message'] ?: $tagihan['message'] ?: 'ISBN tidak ditemukan pada API Perpusnas.';

        return [
            'verified' => false,
            'message' => $errorMessage,
            'source' => 'api',
            'raw' => [
                'detail' => $detail['raw'],
                'tagihan' => $tagihan['raw'],
            ],
        ];
    }

    private function postJson(string $path, array $payload): array
    {
        $creds = $this->credentials();
        $body = array_merge($payload, [
            'token' => $creds['token'],
            'username' => $creds['username'],
            'password' => $creds['password'],
        ]);

        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'verify' => (bool) config('services.perpusnas.verify_ssl', true),
                ])
                ->post(rtrim((string) config('services.perpusnas.base_url'), '/') . $path, $body);

            $json = $response->json();
            $isSuccessStatus = strtolower((string) data_get($json, 'status')) === 'success';
            $data = data_get($json, 'data', []);
            $dataRows = is_array($data) ? $data : [];

            return [
                'ok' => $response->ok() && $isSuccessStatus,
                'message' => (string) (data_get($json, 'message') ?: ''),
                'data' => $dataRows,
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'raw' => null,
            ];
        }
    }

    private function matchRows(array $rows, string $isbnDigits, ?string $title): ?array
    {
        foreach ($rows as $row) {
            $rowIsbn = $this->normalizeIsbn((string) data_get($row, 'ISBN_NO', ''));
            $rowTitle = (string) data_get($row, 'TITLE', '');

            if ($rowIsbn !== '' && $rowIsbn === $isbnDigits) {
                if ($title === null || trim($title) === '') {
                    return $row;
                }

                if ($this->titleLooksSimilar($title, $rowTitle)) {
                    return $row;
                }
            }
        }

        return null;
    }

    private function titleLooksSimilar(string $a, string $b): bool
    {
        $cleanA = $this->normalizeText($a);
        $cleanB = $this->normalizeText($b);

        if ($cleanA === '' || $cleanB === '') {
            return false;
        }

        if (str_contains($cleanA, $cleanB) || str_contains($cleanB, $cleanA)) {
            return true;
        }

        $tokens = array_values(array_filter(explode(' ', $cleanA), fn($token) => strlen($token) >= 4));
        if (empty($tokens)) {
            return false;
        }

        $matched = 0;
        foreach ($tokens as $token) {
            if (str_contains($cleanB, $token)) {
                $matched++;
            }
        }

        return $matched >= min(2, count($tokens));
    }

    private function normalizeIsbn(string $isbn): string
    {
        return preg_replace('/[^0-9Xx]/', '', $isbn) ?? '';
    }

    private function normalizeText(string $text): string
    {
        $lower = mb_strtolower($text);
        $normalized = preg_replace('/[^a-z0-9\s]/u', ' ', $lower) ?? '';
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? '';

        return $normalized;
    }

    private function credentials(): array
    {
        $token = (string) config('services.perpusnas.token', '');
        $username = (string) config('services.perpusnas.username', '');
        $password = (string) config('services.perpusnas.password', '');

        return [
            'token' => $token,
            'username' => $username,
            'password' => $password,
            'ready' => $token !== '' && $username !== '' && $password !== '',
        ];
    }
}
