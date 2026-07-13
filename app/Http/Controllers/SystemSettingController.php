<?php

namespace App\Http\Controllers;

use App\Models\SystemSettingAudit;
use App\Services\LicenseService;
use App\Services\PublishingOverageService;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemSettingController extends Controller
{
    public function index(Request $request, SystemSettingService $settings, PublishingOverageService $overage)
    {
        /** @var LicenseService $license */
        $license = app(LicenseService::class);

        $openRouterApiKey = (string) $settings->get('assistant.openrouter_api_key', config('services.openrouter.api_key', ''));
        $openAiApiKey = (string) $settings->get('assistant.openai_api_key', config('services.openai.api_key', ''));
        $licenseCode = (string) $settings->get('license.code', '');
        $licenseValidation = $license->validationDetails($licenseCode);
        $revokedHashes = json_decode((string) $settings->get('license.revoked_hashes', '[]'), true);

        if (!is_array($revokedHashes)) {
            $revokedHashes = [];
        }

        $keyFilter = trim((string) $request->query('audit_key', ''));
        $actorFilter = trim((string) $request->query('audit_actor', ''));

        $auditQuery = SystemSettingAudit::query()->latest('id');

        if ($keyFilter !== '') {
            $auditQuery->where('key', 'like', '%' . $keyFilter . '%');
        }

        if ($actorFilter !== '' && is_numeric($actorFilter)) {
            $auditQuery->where('changed_by', (int) $actorFilter);
        }

        return view('settings.system', [
            'values' => [
                'openrouter_model' => (string) $settings->get('assistant.openrouter_model', config('services.openrouter.model', '')),
                'openrouter_base_url' => (string) $settings->get('assistant.openrouter_base_url', config('services.openrouter.base_url', 'https://openrouter.ai/api/v1')),
                'openrouter_verify_ssl' => $settings->getBool('assistant.openrouter_verify_ssl', (bool) config('services.openrouter.verify_ssl', true)),
                'openai_model' => (string) $settings->get('assistant.openai_model', config('services.openai.model', 'gpt-4o-mini')),
                'openai_base_url' => (string) $settings->get('assistant.openai_base_url', config('services.openai.base_url', 'https://api.openai.com/v1')),
                'openai_verify_ssl' => $settings->getBool('assistant.openai_verify_ssl', (bool) config('services.openai.verify_ssl', true)),
                'system_brand_name' => (string) $settings->get('system.brand_name', config('app.name', 'MS ISBN Publishing')),
                'assistant_enabled' => $settings->getBool('feature.assistant_enabled', true),
                'license_required' => $settings->getBool('license.required', true),
                'license_customer_name' => (string) $settings->get('license.customer_name', ''),
                'license_customer_email' => (string) $settings->get('license.customer_email', ''),
                'license_domain' => (string) $settings->get('license.domain', $license->currentDomain()),
                'license_expires_at' => (string) $settings->get('license.expires_at', ''),
                'license_plan' => (string) $settings->get('license.plan', 'standard'),
                'license_trial' => $settings->getBool('license.trial', false),
                'assignment_warning_hours' => (string) $settings->get('workflow.assignment_warning_hours', '24'),
                'assistant_temperature' => (string) $settings->get('assistant.temperature', '0.2'),
                'tracking_verification_enabled' => $settings->getBool('tracking.verification_enabled', false),
                'tracking_allowed_channels' => (string) $settings->get('tracking.allowed_channels', 'phone,email,whatsapp'),
                'tracking_otp_expiration_minutes' => (string) $settings->get('tracking.otp_expiration_minutes', '10'),
                'ipaymu_api_key' => (string) $settings->get('payment.ipaymu_api_key', config('services.ipaymu.api_key', '')),
                'ipaymu_va' => (string) $settings->get('payment.ipaymu_va', config('services.ipaymu.va', '')),
                'ipaymu_base_url' => (string) $settings->get('payment.ipaymu_base_url', config('services.ipaymu.base_url', 'https://my.ipaymu.com/api/v2')),
                'ipaymu_verify_ssl' => $settings->getBool('payment.ipaymu_verify_ssl', (bool) config('services.ipaymu.verify_ssl', true)),
                'rajaongkir_key' => (string) $settings->get('shipping.rajaongkir_api_key', config('services.rajaongkir.key', '')),
                'rajaongkir_origin_city_id' => (string) $settings->get('shipping.rajaongkir_origin_city_id', config('services.rajaongkir.origin_city_id', '1585')),
                'rajaongkir_verify_ssl' => $settings->getBool('shipping.rajaongkir_verify_ssl', (bool) config('services.rajaongkir.verify_ssl', true)),
                'perpusnas_base_url' => (string) $settings->get('integrations.perpusnas_base_url', config('services.perpusnas.base_url', 'https://api-penerbitsakedap.perpusnas.go.id')),
                'perpusnas_token' => (string) $settings->get('integrations.perpusnas_token', config('services.perpusnas.token', '')),
                'perpusnas_username' => (string) $settings->get('integrations.perpusnas_username', config('services.perpusnas.username', '')),
                'perpusnas_password' => (string) $settings->get('integrations.perpusnas_password', config('services.perpusnas.password', '')),
                'perpusnas_verify_ssl' => $settings->getBool('integrations.perpusnas_verify_ssl', (bool) config('services.perpusnas.verify_ssl', true)),
                'reminder_email_enabled' => $settings->getBool('reminder.email_enabled', (bool) config('services.reminder.email_enabled', false)),
                'reminder_whatsapp_enabled' => $settings->getBool('reminder.whatsapp_enabled', (bool) config('services.reminder.whatsapp_enabled', false)),
                'reminder_sms_enabled' => $settings->getBool('reminder.sms_enabled', (bool) config('services.reminder.sms_enabled', false)),
                'reminder_whatsapp_webhook_url' => (string) $settings->get('reminder.whatsapp_webhook_url', config('services.reminder.whatsapp_webhook_url', '')),
                'reminder_sms_webhook_url' => (string) $settings->get('reminder.sms_webhook_url', config('services.reminder.sms_webhook_url', '')),
                'publishing_a4_layout_limit' => (string) $settings->get('publishing.a4_layout_limit', (string) $overage->getA4Limit()),
                'publishing_a4_layout_overage_per_page' => (string) $settings->get('publishing.a4_layout_overage_per_page', (string) $overage->getLayoutOveragePerPage()),
                'publishing_a4_editing_overage_per_page' => (string) $settings->get('publishing.a4_editing_overage_per_page', (string) $overage->getEditingOveragePerPage()),
                'publishing_print_overage_rules_json' => (string) $settings->get('publishing.print_overage_rules_json', json_encode($overage->getPrintPaperRules(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            ],
            'effective' => [
                'system_brand_name_source' => $settings->has('system.brand_name') ? 'database' : 'env/config',
                'openrouter_api_key' => $settings->maskSecret($openRouterApiKey),
                'openrouter_api_key_source' => $settings->has('assistant.openrouter_api_key') ? 'database' : 'env/config',
                'openai_api_key' => $settings->maskSecret($openAiApiKey),
                'openai_api_key_source' => $settings->has('assistant.openai_api_key') ? 'database' : 'env/config',
                'license_code' => $settings->maskSecret($licenseCode),
                'license_code_source' => $settings->has('license.code') ? 'database' : 'env/config',
                'license_required_source' => $settings->has('license.required') ? 'database' : 'env/config',
                'license_issuer_secret_source' => $settings->has('license.issuer_secret') ? 'database' : 'env/config',
                'license_valid' => $licenseValidation['valid'],
                'license_type' => $licenseValidation['type'],
                'license_reason' => $licenseValidation['reason'],
                'license_revoked_count' => count($revokedHashes),
                'owner_license_code' => $license->expectedOwnerLicenseCode(),
                'ipaymu_api_key_source' => $settings->has('payment.ipaymu_api_key') ? 'database' : 'env/config',
                'ipaymu_va_source' => $settings->has('payment.ipaymu_va') ? 'database' : 'env/config',
                'ipaymu_base_url_source' => $settings->has('payment.ipaymu_base_url') ? 'database' : 'env/config',
                'ipaymu_verify_ssl_source' => $settings->has('payment.ipaymu_verify_ssl') ? 'database' : 'env/config',
                'rajaongkir_key_source' => $settings->has('shipping.rajaongkir_api_key') ? 'database' : 'env/config',
                'rajaongkir_origin_city_id_source' => $settings->has('shipping.rajaongkir_origin_city_id') ? 'database' : 'env/config',
                'rajaongkir_verify_ssl_source' => $settings->has('shipping.rajaongkir_verify_ssl') ? 'database' : 'env/config',
                'perpusnas_base_url_source' => $settings->has('integrations.perpusnas_base_url') ? 'database' : 'env/config',
                'perpusnas_token_source' => $settings->has('integrations.perpusnas_token') ? 'database' : 'env/config',
                'perpusnas_username_source' => $settings->has('integrations.perpusnas_username') ? 'database' : 'env/config',
                'perpusnas_password_source' => $settings->has('integrations.perpusnas_password') ? 'database' : 'env/config',
                'perpusnas_verify_ssl_source' => $settings->has('integrations.perpusnas_verify_ssl') ? 'database' : 'env/config',
                'reminder_email_enabled_source' => $settings->has('reminder.email_enabled') ? 'database' : 'env/config',
                'reminder_whatsapp_enabled_source' => $settings->has('reminder.whatsapp_enabled') ? 'database' : 'env/config',
                'reminder_sms_enabled_source' => $settings->has('reminder.sms_enabled') ? 'database' : 'env/config',
                'reminder_whatsapp_webhook_url_source' => $settings->has('reminder.whatsapp_webhook_url') ? 'database' : 'env/config',
                'reminder_sms_webhook_url_source' => $settings->has('reminder.sms_webhook_url') ? 'database' : 'env/config',
                'publishing_a4_layout_limit_source' => $settings->has('publishing.a4_layout_limit') ? 'database' : 'default',
                'publishing_a4_layout_overage_per_page_source' => $settings->has('publishing.a4_layout_overage_per_page') ? 'database' : 'default',
                'publishing_a4_editing_overage_per_page_source' => $settings->has('publishing.a4_editing_overage_per_page') ? 'database' : 'default',
                'publishing_print_overage_rules_json_source' => $settings->has('publishing.print_overage_rules_json') ? 'database' : 'default',
            ],
            'audits' => $auditQuery->limit(20)->get(),
            'filters' => [
                'audit_key' => $keyFilter,
                'audit_actor' => $actorFilter,
            ],
        ]);
    }

    public function exportAuditCsv(Request $request): StreamedResponse
    {
        $keyFilter = trim((string) $request->query('audit_key', ''));
        $actorFilter = trim((string) $request->query('audit_actor', ''));

        $query = SystemSettingAudit::query();

        if ($keyFilter !== '') {
            $query->where('key', 'like', '%' . $keyFilter . '%');
        }

        if ($actorFilter !== '' && is_numeric($actorFilter)) {
            $query->where('changed_by', (int) $actorFilter);
        }

        $filename = 'system-settings-audits-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['id', 'created_at', 'key', 'old_value', 'new_value', 'changed_by', 'is_sensitive']);

            $query->orderBy('id')->chunkById(500, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        (string) $row->id,
                        optional($row->created_at)->format('Y-m-d H:i:s'),
                        (string) $row->key,
                        $row->old_value,
                        $row->new_value,
                        $row->changed_by,
                        $row->is_sensitive ? '1' : '0',
                    ]);
                }
            }, 'id');

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function update(Request $request, SystemSettingService $settings): RedirectResponse
    {
        /** @var LicenseService $license */
        $license = app(LicenseService::class);

        $data = $request->validate([
            'openrouter_api_key' => ['nullable', 'string', 'max:4000'],
            'openrouter_model' => ['nullable', 'string', 'max:255'],
            'openrouter_base_url' => ['nullable', 'url', 'max:255'],
            'openai_api_key' => ['nullable', 'string', 'max:4000'],
            'openai_model' => ['nullable', 'string', 'max:255'],
            'openai_base_url' => ['nullable', 'url', 'max:255'],
            'system_brand_name' => ['nullable', 'string', 'max:120'],
            'license_code' => ['nullable', 'string', 'max:4000'],
            'license_issuer_secret' => ['nullable', 'string', 'max:4000'],
            'license_customer_name' => ['nullable', 'string', 'max:120'],
            'license_customer_email' => ['nullable', 'email', 'max:180'],
            'license_domain' => ['nullable', 'string', 'max:255'],
            'license_expires_at' => ['nullable', 'date'],
            'license_plan' => ['nullable', 'string', 'max:120'],
            'assignment_warning_hours' => ['nullable', 'integer', 'min:1', 'max:240'],
            'assistant_temperature' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'tracking_allowed_channels' => ['nullable', 'string', 'max:64'],
            'tracking_otp_expiration_minutes' => ['nullable', 'integer', 'min:1', 'max:30'],
            'ipaymu_api_key' => ['nullable', 'string', 'max:4000'],
            'ipaymu_va' => ['nullable', 'string', 'max:120'],
            'ipaymu_base_url' => ['nullable', 'url', 'max:255'],
            'rajaongkir_key' => ['nullable', 'string', 'max:300'],
            'rajaongkir_origin_city_id' => ['nullable', 'string', 'max:32'],
            'perpusnas_base_url' => ['nullable', 'url', 'max:255'],
            'perpusnas_token' => ['nullable', 'string', 'max:4000'],
            'perpusnas_username' => ['nullable', 'string', 'max:255'],
            'perpusnas_password' => ['nullable', 'string', 'max:4000'],
            'reminder_whatsapp_webhook_url' => ['nullable', 'url', 'max:255'],
            'reminder_sms_webhook_url' => ['nullable', 'url', 'max:255'],
            'publishing_a4_layout_limit' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'publishing_a4_layout_overage_per_page' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'publishing_a4_editing_overage_per_page' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'publishing_print_overage_rules_json' => ['nullable', 'string', 'max:20000'],
        ]);

        if (!empty($data['publishing_print_overage_rules_json'])) {
            $decoded = json_decode((string) $data['publishing_print_overage_rules_json'], true);
            if (!is_array($decoded)) {
                return back()->withInput()->withErrors([
                    'publishing_print_overage_rules_json' => 'Format JSON aturan biaya cetak tidak valid.',
                ]);
            }
        }

        $payload = [
            'assistant.openrouter_model' => $this->nullableTrim($data['openrouter_model'] ?? null),
            'assistant.openrouter_base_url' => $this->nullableTrim($data['openrouter_base_url'] ?? null),
            'assistant.openrouter_verify_ssl' => $request->boolean('openrouter_verify_ssl') ? '1' : '0',
            'assistant.openai_model' => $this->nullableTrim($data['openai_model'] ?? null),
            'assistant.openai_base_url' => $this->nullableTrim($data['openai_base_url'] ?? null),
            'assistant.openai_verify_ssl' => $request->boolean('openai_verify_ssl') ? '1' : '0',
            'system.brand_name' => $this->nullableTrim($data['system_brand_name'] ?? null),
            'license.required' => $request->boolean('license_required') ? '1' : '0',
            'license.customer_name' => $this->nullableTrim($data['license_customer_name'] ?? null),
            'license.customer_email' => $this->nullableTrim($data['license_customer_email'] ?? null),
            'license.domain' => $this->nullableTrim($data['license_domain'] ?? null),
            'license.expires_at' => $this->nullableTrim($data['license_expires_at'] ?? null),
            'license.plan' => $this->nullableTrim($data['license_plan'] ?? null),
            'license.trial' => $request->boolean('license_trial') ? '1' : '0',
            'feature.assistant_enabled' => $request->boolean('assistant_enabled') ? '1' : '0',
            'workflow.assignment_warning_hours' => isset($data['assignment_warning_hours']) ? (string) $data['assignment_warning_hours'] : null,
            'assistant.temperature' => isset($data['assistant_temperature']) ? (string) $data['assistant_temperature'] : null,
            'tracking.verification_enabled' => $request->boolean('tracking_verification_enabled') ? '1' : '0',
            'tracking.allowed_channels' => $this->nullableTrim($data['tracking_allowed_channels'] ?? null),
            'tracking.otp_expiration_minutes' => isset($data['tracking_otp_expiration_minutes']) ? (string) $data['tracking_otp_expiration_minutes'] : null,
            'payment.ipaymu_api_key' => $this->nullableTrim($data['ipaymu_api_key'] ?? null),
            'payment.ipaymu_va' => $this->nullableTrim($data['ipaymu_va'] ?? null),
            'payment.ipaymu_base_url' => $this->nullableTrim($data['ipaymu_base_url'] ?? null),
            'payment.ipaymu_verify_ssl' => $request->boolean('ipaymu_verify_ssl') ? '1' : '0',
            'shipping.rajaongkir_api_key' => $this->nullableTrim($data['rajaongkir_key'] ?? null),
            'shipping.rajaongkir_origin_city_id' => $this->nullableTrim($data['rajaongkir_origin_city_id'] ?? null),
            'shipping.rajaongkir_verify_ssl' => $request->boolean('rajaongkir_verify_ssl') ? '1' : '0',
            'integrations.perpusnas_base_url' => $this->nullableTrim($data['perpusnas_base_url'] ?? null),
            'integrations.perpusnas_token' => $this->nullableTrim($data['perpusnas_token'] ?? null),
            'integrations.perpusnas_username' => $this->nullableTrim($data['perpusnas_username'] ?? null),
            'integrations.perpusnas_password' => $this->nullableTrim($data['perpusnas_password'] ?? null),
            'integrations.perpusnas_verify_ssl' => $request->boolean('perpusnas_verify_ssl') ? '1' : '0',
            'reminder.email_enabled' => $request->boolean('reminder_email_enabled') ? '1' : '0',
            'reminder.whatsapp_enabled' => $request->boolean('reminder_whatsapp_enabled') ? '1' : '0',
            'reminder.sms_enabled' => $request->boolean('reminder_sms_enabled') ? '1' : '0',
            'reminder.whatsapp_webhook_url' => $this->nullableTrim($data['reminder_whatsapp_webhook_url'] ?? null),
            'reminder.sms_webhook_url' => $this->nullableTrim($data['reminder_sms_webhook_url'] ?? null),
            'publishing.a4_layout_limit' => isset($data['publishing_a4_layout_limit']) ? (string) $data['publishing_a4_layout_limit'] : null,
            'publishing.a4_layout_overage_per_page' => isset($data['publishing_a4_layout_overage_per_page']) ? (string) $data['publishing_a4_layout_overage_per_page'] : null,
            'publishing.a4_editing_overage_per_page' => isset($data['publishing_a4_editing_overage_per_page']) ? (string) $data['publishing_a4_editing_overage_per_page'] : null,
            'publishing.print_overage_rules_json' => $this->nullableTrim($data['publishing_print_overage_rules_json'] ?? null),
        ];

        if ($request->boolean('clear_license_issuer_secret')) {
            $payload['license.issuer_secret'] = null;
        } else {
            $newIssuerSecret = $this->nullableTrim($data['license_issuer_secret'] ?? null);
            if ($newIssuerSecret !== null) {
                $payload['license.issuer_secret'] = $newIssuerSecret;
            }
        }

        if ($request->boolean('clear_license_code')) {
            $payload['license.code'] = null;
        } else {
            $newLicenseCode = $this->nullableTrim($data['license_code'] ?? null);
            if ($newLicenseCode !== null) {
                $payload['license.code'] = strtoupper($newLicenseCode);
            }
        }

        if ($request->boolean('revoke_active_license')) {
            $current = (string) $settings->get('license.code', '');
            if ($current !== '' && $license->isCommercialToken($current)) {
                $json = (string) $settings->get('license.revoked_hashes', '[]');
                $revoked = json_decode($json, true);
                if (!is_array($revoked)) {
                    $revoked = [];
                }
                $hash = $license->tokenHash($current);
                if (!in_array($hash, $revoked, true)) {
                    $revoked[] = $hash;
                }
                $payload['license.revoked_hashes'] = json_encode($revoked, JSON_UNESCAPED_UNICODE);
            }
            $payload['license.code'] = null;
        }

        if ($request->boolean('generate_commercial_license')) {
            try {
                $token = $license->generateCommercialToken([
                    'customer_name' => (string) ($data['license_customer_name'] ?? ''),
                    'customer_email' => $this->nullableTrim($data['license_customer_email'] ?? null),
                    'domain' => (string) ($data['license_domain'] ?? ''),
                    'expires_at' => $this->nullableTrim($data['license_expires_at'] ?? null),
                    'plan' => $this->nullableTrim($data['license_plan'] ?? null),
                    'trial' => $request->boolean('license_trial'),
                ]);

                $payload['license.code'] = $token;
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'license_generation' => $e->getMessage(),
                    ]);
            }
        }

        if ($request->boolean('clear_openrouter_api_key')) {
            $payload['assistant.openrouter_api_key'] = null;
        } else {
            $newOpenRouterKey = $this->nullableTrim($data['openrouter_api_key'] ?? null);
            if ($newOpenRouterKey !== null) {
                $payload['assistant.openrouter_api_key'] = $newOpenRouterKey;
            }
        }

        if ($request->boolean('clear_openai_api_key')) {
            $payload['assistant.openai_api_key'] = null;
        } else {
            $newOpenAiKey = $this->nullableTrim($data['openai_api_key'] ?? null);
            if ($newOpenAiKey !== null) {
                $payload['assistant.openai_api_key'] = $newOpenAiKey;
            }
        }

        $settings->setMany($payload, (int) $request->user()->id);

        return back()->with('success', 'System settings berhasil diperbarui.');
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
