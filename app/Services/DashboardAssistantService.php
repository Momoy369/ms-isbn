<?php

namespace App\Services;

use App\Models\AssistantChatLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SystemSettingService;

class DashboardAssistantService
{
    /**
     * @param array<string, mixed> $context
     */
    public function ask(User $user, string $question, array $context = []): array
    {
        $settings = app(SystemSettingService::class);

        if (!$settings->getBool('feature.assistant_enabled', true)) {
            return [
                'answer' => 'AI Asisten sedang dinonaktifkan sementara oleh superadmin. Silakan hubungi admin untuk mengaktifkan kembali.',
                'source' => 'disabled',
            ];
        }

        $question = trim($question);

        if ($question === '') {
            return [
                'answer' => 'Pertanyaan masih kosong. Silakan tulis pertanyaan Anda, misalnya: "cara cek invoice" atau "fitur di dashboard saya apa saja?"',
                'source' => 'validation',
            ];
        }

        $guarded = $this->checkRestrictedModuleQuestion($user, $question);

        if ($guarded !== null) {
            $this->storeLog($user, $question, $guarded, 'guard', $context);

            return [
                'answer' => $guarded,
                'source' => 'guard',
            ];
        }

        $openRouterReason = '';
        $openAiReason = '';

        $ai = $this->askWithOpenRouter($user, $question, $context, $openRouterReason);

        $source = 'openrouter';

        if ($ai === null) {
            $ai = $this->askWithOpenAi($user, $question, $context, $openAiReason);
            $source = 'openai';
        }

        if ($ai !== null) {
            $this->storeLog($user, $question, $ai, $source, $context);

            return [
                'answer' => $ai,
                'source' => $source,
            ];
        }

        $fallback = $this->askWithLocalKnowledge($user, $question, $context);
        $fallbackSource = sprintf(
            'local(fallback:openrouter:%s|openai:%s)',
            $openRouterReason !== '' ? $openRouterReason : 'unknown',
            $openAiReason !== '' ? $openAiReason : 'unknown'
        );
        $this->storeLog($user, $question, $fallback, $fallbackSource, $context);

        return [
            'answer' => $fallback,
            'source' => $fallbackSource,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function askWithOpenRouter(User $user, string $question, array $context, string &$reason = ''): ?string
    {
        $settings = app(SystemSettingService::class);
        $apiKey = trim((string) $settings->get('assistant.openrouter_api_key', config('services.openrouter.api_key')));
        $verifySsl = $settings->getBool('assistant.openrouter_verify_ssl', (bool) config('services.openrouter.verify_ssl', true));
        $temperature = (float) $settings->get('assistant.temperature', 0.2);

        if ($temperature < 0) {
            $temperature = 0;
        }
        if ($temperature > 1) {
            $temperature = 1;
        }

        if ($apiKey === '') {
            $reason = 'no_key';
            return null;
        }

        $baseUrl = rtrim((string) $settings->get('assistant.openrouter_base_url', config('services.openrouter.base_url', 'https://openrouter.ai/api/v1')), '/');
        $configuredModels = (string) $settings->get('assistant.openrouter_model', config('services.openrouter.model', ''));
        $configuredList = array_values(array_filter(array_map('trim', explode(',', $configuredModels))));
        $fallbackList = [
            'meta-llama/llama-3.1-8b-instruct:free',
            'deepseek/deepseek-r1-0528:free',
            'mistralai/mistral-7b-instruct:free',
        ];
        $models = array_values(array_unique(array_merge($configuredList, $fallbackList)));

        $systemPrompt = $this->buildSystemPrompt($user, $context);

        foreach ($models as $model) {
            try {
                $http = Http::timeout(35)
                    ->connectTimeout(12)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->asJson()
                    ->withHeaders([
                        'HTTP-Referer' => (string) config('app.url', 'http://localhost'),
                        'X-Title' => (string) $settings->get('system.brand_name', 'MS ISBN Dashboard Assistant'),
                    ]);

                if (!$verifySsl) {
                    $http = $http->withoutVerifying();
                }

                $response = $http->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'temperature' => $temperature,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $question,
                        ],
                    ],
                ]);

                if (!$response->successful()) {
                    $reason = 'http_' . $response->status();
                    Log::warning('Assistant OpenRouter request failed', [
                        'model' => $model,
                        'status' => $response->status(),
                        'body' => mb_substr((string) $response->body(), 0, 500),
                    ]);
                    continue;
                }

                $text = (string) data_get($response->json(), 'choices.0.message.content', '');
                $text = trim($text);

                if ($text !== '') {
                    $reason = '';
                    return $text;
                }

                $reason = 'empty_response';
            } catch (\Throwable $e) {
                $reason = 'exception';
                if (str_contains(strtolower($e->getMessage()), 'error setting certificate file')) {
                    $reason = 'ca_cert_invalid';
                }
                Log::warning('Assistant OpenRouter exception', [
                    'model' => $model,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function askWithOpenAi(User $user, string $question, array $context, string &$reason = ''): ?string
    {
        $settings = app(SystemSettingService::class);
        $apiKey = trim((string) $settings->get('assistant.openai_api_key', config('services.openai.api_key')));
        $verifySsl = $settings->getBool('assistant.openai_verify_ssl', (bool) config('services.openai.verify_ssl', true));
        $temperature = (float) $settings->get('assistant.temperature', 0.2);

        if ($temperature < 0) {
            $temperature = 0;
        }
        if ($temperature > 1) {
            $temperature = 1;
        }

        if ($apiKey === '') {
            $reason = 'no_key';
            return null;
        }

        $baseUrl = rtrim((string) $settings->get('assistant.openai_base_url', config('services.openai.base_url', 'https://api.openai.com/v1')), '/');
        $model = (string) $settings->get('assistant.openai_model', config('services.openai.model', 'gpt-4o-mini'));

        $systemPrompt = $this->buildSystemPrompt($user, $context);

        try {
            $http = Http::timeout(30)
                ->connectTimeout(12)
                ->withToken($apiKey)
                ->acceptJson()
                ->asJson();

            if (!$verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'temperature' => $temperature,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $question,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                $reason = 'http_' . $response->status();
                Log::warning('Assistant OpenAI request failed', [
                    'status' => $response->status(),
                    'body' => mb_substr((string) $response->body(), 0, 500),
                ]);
                return null;
            }

            $text = (string) data_get($response->json(), 'choices.0.message.content', '');
            $text = trim($text);

            if ($text === '') {
                $reason = 'empty_response';
                return null;
            }

            $reason = '';
            return $text;
        } catch (\Throwable $e) {
            $reason = 'exception';
            Log::warning('Assistant OpenAI exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function askWithLocalKnowledge(User $user, string $question, array $context): string
    {
        $q = mb_strtolower($question);
        $contextLine = $this->buildContextLine($context);

        if (str_contains($q, 'dashboard') || str_contains($q, 'menu') || str_contains($q, 'fitur saya')) {
            return trim($this->buildRoleFeatureAnswer($user) . "\n" . $contextLine);
        }

        if (str_contains($q, 'hitung halaman') || str_contains($q, 'a4') || str_contains($q, 'unesco') || str_contains($q, 'b5')) {
            return implode("\n", [
                'Untuk fitur Hitung Halaman Naskah:',
                '1. Buka menu "Hitung Halaman Naskah".',
                '2. Pilih ukuran utama (A4/A5/B5/UNESCO).',
                '3. Opsional: isi ukuran pembanding.',
                '4. Upload file DOCX lalu klik hitung.',
                '5. Sistem menampilkan jumlah halaman utama + perbandingan (jika diisi).',
            ]);
        }

        if (str_contains($q, 'invoice') || str_contains($q, 'pembayaran')) {
            return implode("\n", [
                'Akses modul invoice bergantung role:',
                '- Author: menu Invoice Penulis.',
                '- Finance/Admin/Owner: menu Finance Invoice.',
                'Tips: cari status invoice terlebih dahulu (pending/paid) sebelum lanjut aksi pembayaran.',
            ]);
        }

        if (str_contains($q, 'ruang file') || str_contains($q, 'role file') || str_contains($q, 'upload file')) {
            return implode("\n", [
                'Untuk Ruang File Role:',
                '1. Buka menu Ruang File Role.',
                '2. Pilih folder/role lalu upload file.',
                '3. Atur akses file (private/role/all_roles/public) bila diperlukan.',
                '4. Gunakan fitur rename/move/share dari aksi file.',
            ]);
        }

        return implode("\n", [
            'Saya bisa bantu jelaskan fitur dashboard Anda langkah demi langkah.',
            'Coba pertanyaan yang lebih spesifik, contoh:',
            '- "fitur apa saja di dashboard role saya?"',
            '- "cara pakai hitung halaman naskah"',
            '- "cara cek invoice dan status pembayaran"',
            '- "cara upload file di ruang file role"',
            '',
            $this->buildRoleFeatureAnswer($user),
            $contextLine,
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function buildSystemPrompt(User $user, array $context): string
    {
        $settings = app(SystemSettingService::class);
        $brandName = (string) $settings->get('system.brand_name', 'MS ISBN Publishing');
        $role = (string) $user->role;
        $readme = $this->readKnowledgeFile(base_path('README.md'));
        $manual = $this->readKnowledgeFile(base_path('docs/manual-book-sistem.md'));
        $contextSummary = $this->buildContextLine($context);

        $roleFeatures = $this->roleFeatureMap()[$role] ?? [];

        $featuresText = empty($roleFeatures)
            ? '- (belum ada daftar spesifik role)'
            : implode("\n", array_map(static fn($f) => '- ' . $f, $roleFeatures));

        return <<<PROMPT
Anda adalah AI Asisten untuk sistem MS ISBN Publishing.
Brand sistem: {$brandName}
Tugas Anda: menjawab pertanyaan user tentang fitur, cara penggunaan fitur, dan navigasi dashboard sesuai role user.

Aturan jawaban:
- Jawab dalam Bahasa Indonesia yang ringkas dan praktis.
- Fokus pada langkah operasional (step-by-step) jika user menanyakan "cara".
- Jika user bertanya tentang menu/dashboard, prioritaskan fitur role user berikut:
{$featuresText}
- Jika informasi tidak tersedia, katakan dengan jujur dan beri alternatif menu terdekat.
- Jangan mengarang route/fitur yang tidak ada.

Role user saat ini: {$role}
Konteks halaman user saat ini: {$contextSummary}

Ringkasan dokumen internal:
[README]
{$readme}

[MANUAL BOOK]
{$manual}
PROMPT;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function buildContextLine(array $context): string
    {
        $path = (string) ($context['current_path'] ?? '');

        if ($path === '') {
            return 'Konteks halaman belum dikirim oleh client.';
        }

        $menuHint = 'menu umum';

        $map = [
            '/dashboard' => 'Dashboard Backoffice',
            '/author' => 'Dashboard Author',
            '/customer' => 'Dashboard Customer',
            '/books' => 'Modul Naskah',
            '/assignments' => 'Modul Assignment',
            '/finance' => 'Modul Finance',
            '/role-files' => 'Ruang File Role',
            '/tools/manuscript-page-counter' => 'Tool Hitung Halaman Naskah',
            '/production' => 'Dashboard Operasional',
            '/store' => 'Storefront',
        ];

        foreach ($map as $prefix => $label) {
            if (str_starts_with($path, $prefix)) {
                $menuHint = $label;
                break;
            }
        }

        return 'Halaman aktif: ' . $path . ' (konteks menu: ' . $menuHint . ').';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function storeLog(User $user, string $question, string $answer, string $source, array $context): void
    {
        try {
            AssistantChatLog::query()->create([
                'user_id' => $user->id,
                'question' => $question,
                'answer' => $answer,
                'source' => $source,
                'context' => [
                    'current_path' => (string) ($context['current_path'] ?? ''),
                    'page_title' => (string) ($context['page_title'] ?? ''),
                ],
            ]);
        } catch (\Throwable $e) {
            // Keep assistant response functional even when logging fails.
        }
    }

    private function readKnowledgeFile(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        $raw = (string) file_get_contents($path);
        $raw = preg_replace('/\s+/', ' ', $raw) ?? '';

        return mb_substr(trim($raw), 0, 12000);
    }

    private function buildRoleFeatureAnswer(User $user): string
    {
        $role = (string) $user->role;
        $features = $this->roleFeatureMap()[$role] ?? [];

        if (empty($features)) {
            return 'Saya belum menemukan daftar fitur spesifik untuk role Anda. Silakan buka sidebar utama untuk melihat menu yang tersedia.';
        }

        $lines = [
            'Fitur utama yang biasanya tersedia di dashboard Anda:',
        ];

        foreach ($features as $feature) {
            $lines[] = '- ' . $feature;
        }

        $lines[] = 'Jika Anda mau, saya bisa jelaskan langkah detail untuk salah satu fitur di atas.';

        return implode("\n", $lines);
    }

    private function roleFeatureMap(): array
    {
        return [
            'admin' => [
                'Dashboard backoffice dan dashboard operasional',
                'Naskah, assignment, workflow, ISBN queue',
                'Ruang File Role',
                'Finance invoice, royalti, order storefront',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi internal',
            ],
            'superadmin' => [
                'Akses penuh modul internal seperti admin',
                'Monitoring dashboard operasional dan approval',
                'Tool Hitung Halaman Naskah',
                'Ruang File Role dan kontrol lintas modul',
            ],
            'owner' => [
                'Dashboard internal + operasional',
                'Finance, royalti, order storefront',
                'Tool Hitung Halaman Naskah',
                'Workspace produksi dan papan pribadi',
            ],
            'finance' => [
                'Finance invoice dan royalti',
                'Order storefront, voucher, laporan ekspor',
                'Dashboard operasional gabungan',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi',
            ],
            'isbn' => [
                'Antrian ISBN dan approval ISBN',
                'Naskah dan proses readiness ISBN',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi',
            ],
            'editor' => [
                'Assignment saya dan timeline produksi',
                'Workspace produksi / role files',
                'Dashboard operasional',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi',
            ],
            'layouter' => [
                'Assignment saya dan layout workflow',
                'Layout generator dan workspace produksi',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi',
            ],
            'designer' => [
                'Assignment saya dan workflow desain',
                'Workspace produksi',
                'Tool Hitung Halaman Naskah',
                'Papan Pribadi',
            ],
            'author' => [
                'Dashboard author',
                'Invoice penulis, order penulis, royalti',
                'Claim buku penulis',
                'Akses storefront dan dashboard customer',
                'Tool Hitung Halaman Naskah',
            ],
            'customer' => [
                'Dashboard customer',
                'Riwayat order dan invoice store',
                'Library ebook',
                'Akses storefront',
                'Tool Hitung Halaman Naskah',
            ],
            'reader' => [
                'Dashboard customer/reader',
                'Riwayat order dan akses ebook',
                'Akses storefront',
                'Tool Hitung Halaman Naskah',
            ],
        ];
    }

    private function checkRestrictedModuleQuestion(User $user, string $question): ?string
    {
        $q = mb_strtolower($question);
        $role = (string) $user->role;

        $rules = [
            [
                'module' => 'Finance Internal',
                'keywords' => ['finance invoice', 'payout', 'royalti payout', 'external sales', 'legacy books', 'voucher store'],
                'allowed_roles' => ['admin', 'owner', 'finance', 'superadmin'],
            ],
            [
                'module' => 'ISBN Internal',
                'keywords' => ['isbn queue', 'submit isbn', 'approve isbn', 'antrian isbn'],
                'allowed_roles' => ['admin', 'isbn', 'owner', 'superadmin'],
            ],
            [
                'module' => 'User Management',
                'keywords' => ['manage user', 'manajemen user', 'buat user', 'edit user', 'hapus user'],
                'allowed_roles' => ['admin', 'isbn', 'superadmin'],
            ],
            [
                'module' => 'Author Upgrade Review',
                'keywords' => ['review upgrade author', 'approve upgrade author', 'reject upgrade author'],
                'allowed_roles' => ['admin', 'isbn', 'superadmin'],
            ],
        ];

        foreach ($rules as $rule) {
            $hit = false;

            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($q, (string) $keyword)) {
                    $hit = true;
                    break;
                }
            }

            if (!$hit) {
                continue;
            }

            if (in_array($role, $rule['allowed_roles'], true)) {
                return null;
            }

            return 'Maaf, saya tidak bisa memberikan panduan detail untuk modul "' . $rule['module'] . '" karena role Anda tidak memiliki akses modul tersebut.';
        }

        return null;
    }
}
