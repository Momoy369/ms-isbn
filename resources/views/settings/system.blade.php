@extends('adminlte::page')

@section('title', 'System Settings')

@section('content_header')
    <h1 class="m-0 text-dark">System Settings</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Konfigurasi Dinamis (Superadmin)</h3>
        </div>
        <form action="{{ route('settings.system.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    Nilai di halaman ini disimpan di database dan diprioritaskan dibandingkan file <code>.env</code>.
                    Kosongkan field teks jika ingin kembali memakai nilai dari <code>.env</code>.
                </div>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <ul class="nav nav-tabs mb-3" id="system-settings-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tab-business-rules" data-toggle="tab" href="#pane-business-rules"
                            role="tab" aria-controls="pane-business-rules" aria-selected="true">Paket, Redaksi,
                            Percetakan</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tab-general" data-toggle="tab" href="#pane-general" role="tab"
                            aria-controls="pane-general" aria-selected="false">General & Integrasi</a>
                    </li>
                </ul>

                <div class="tab-content" id="system-settings-tab-content">
                    <div class="tab-pane fade show active" id="pane-business-rules" role="tabpanel"
                        aria-labelledby="tab-business-rules">

                        <h5 class="font-weight-bold">Tab Baru: Paket, Redaksi, dan Percetakan</h5>
                        <div class="alert alert-secondary">
                            Gunakan bagian ini untuk aturan biaya dinamis terkait kelebihan halaman naskah dan cetak paket.
                            Nilai dipakai di Author Order dan POS jasa penerbitan.
                        </div>
                        <div class="form-group">
                            <label>Limit Halaman A4 (Layout/Editing)</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['publishing_a4_layout_limit_source'] }}</span>
                            </p>
                            <input type="number" min="1" max="20000" name="publishing_a4_layout_limit"
                                class="form-control"
                                value="{{ old('publishing_a4_layout_limit', $values['publishing_a4_layout_limit']) }}">
                        </div>
                        <div class="form-group">
                            <label>Biaya Lebih Layout per Halaman A4</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['publishing_a4_layout_overage_per_page_source'] }}</span>
                            </p>
                            <input type="number" min="0" step="100" name="publishing_a4_layout_overage_per_page"
                                class="form-control"
                                value="{{ old('publishing_a4_layout_overage_per_page', $values['publishing_a4_layout_overage_per_page']) }}">
                        </div>
                        <div class="form-group">
                            <label>Biaya Lebih Editing per Halaman A4</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['publishing_a4_editing_overage_per_page_source'] }}</span>
                            </p>
                            <input type="number" min="0" step="100"
                                name="publishing_a4_editing_overage_per_page" class="form-control"
                                value="{{ old('publishing_a4_editing_overage_per_page', $values['publishing_a4_editing_overage_per_page']) }}">
                        </div>
                        <div class="form-group mb-4">
                            <label>Aturan Lebih Halaman Cetak per Ukuran (JSON)</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['publishing_print_overage_rules_json_source'] }}</span>
                            </p>
                            <textarea name="publishing_print_overage_rules_json" class="form-control" rows="7" spellcheck="false">{{ old('publishing_print_overage_rules_json', $values['publishing_print_overage_rules_json']) }}</textarea>
                            <small class="text-muted d-block mt-1">Format: array objek dengan key: paper, max_pages,
                                overage_per_page.</small>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="pane-general" role="tabpanel" aria-labelledby="tab-general">

                        <h5 class="font-weight-bold">Lisensi Sistem</h5>
                        <p class="text-muted small mb-2">
                            Status Lisensi:
                            @if ($effective['license_valid'])
                                <span class="badge badge-success">VALID</span>
                            @else
                                <span class="badge badge-danger">BELUM VALID</span>
                            @endif
                            <span class="ml-2 badge badge-secondary">source kode:
                                {{ $effective['license_code_source'] }}</span>
                            <span class="ml-2 badge badge-secondary">source required:
                                {{ $effective['license_required_source'] }}</span>
                            <span class="ml-2 badge badge-info">tipe: {{ $effective['license_type'] }}</span>
                        </p>
                        <p class="text-muted small mb-2">{{ $effective['license_reason'] }}</p>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="license_required" id="license_required" class="form-check-input"
                                value="1" {{ old('license_required', $values['license_required']) ? 'checked' : '' }}>
                            <label for="license_required" class="form-check-label">Wajibkan lisensi untuk menggunakan
                                sistem</label>
                        </div>
                        <div class="form-group">
                            <label>Kode Lisensi Owner (generated)</label>
                            <input type="text" class="form-control" value="{{ $effective['owner_license_code'] }}"
                                readonly>
                            <small class="form-text text-muted">Gunakan kode ini untuk aktivasi lisensi sistem.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label>Issuer Secret (untuk token lisensi komersial)</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['license_issuer_secret_source'] }}</span>
                            </p>
                            <input type="password" name="license_issuer_secret" class="form-control" value=""
                                autocomplete="off"
                                placeholder="Isi secret issuer untuk generate/verifikasi token komersial">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="clear_license_issuer_secret"
                                    name="clear_license_issuer_secret" value="1">
                                <label class="form-check-label" for="clear_license_issuer_secret">Hapus issuer secret dari
                                    database (kembali ke env)</label>
                            </div>
                        </div>
                        <div class="card card-outline card-secondary mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title">Generate Lisensi Komersial</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <input type="text" name="license_customer_name" class="form-control"
                                        value="{{ old('license_customer_name', $values['license_customer_name']) }}"
                                        placeholder="Nama perusahaan/pemilik lisensi">
                                </div>
                                <div class="form-group">
                                    <label>Email Customer</label>
                                    <input type="email" name="license_customer_email" class="form-control"
                                        value="{{ old('license_customer_email', $values['license_customer_email']) }}"
                                        placeholder="customer@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Domain Customer</label>
                                    <input type="text" name="license_domain" class="form-control"
                                        value="{{ old('license_domain', $values['license_domain']) }}"
                                        placeholder="domain.tld">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Expired</label>
                                    <input type="date" name="license_expires_at" class="form-control"
                                        value="{{ old('license_expires_at', $values['license_expires_at']) }}">
                                </div>
                                <div class="form-group">
                                    <label>Plan</label>
                                    <input type="text" name="license_plan" class="form-control"
                                        value="{{ old('license_plan', $values['license_plan']) }}"
                                        placeholder="standard/pro">
                                </div>
                                <div class="form-group form-check mb-2">
                                    <input type="checkbox" name="license_trial" id="license_trial"
                                        class="form-check-input" value="1"
                                        {{ old('license_trial', $values['license_trial']) ? 'checked' : '' }}>
                                    <label for="license_trial" class="form-check-label">Trial License</label>
                                </div>
                                <div class="form-group form-check mb-0">
                                    <input type="checkbox" name="generate_commercial_license"
                                        id="generate_commercial_license" class="form-check-input" value="1">
                                    <label for="generate_commercial_license" class="form-check-label">Generate & set
                                        sebagai
                                        lisensi aktif saat simpan</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label>Masukkan Kode Lisensi Aktif</label>
                            <p class="text-muted small mb-1">Effective Code:
                                <strong>{{ $effective['license_code'] }}</strong></p>
                            <input type="text" name="license_code" class="form-control"
                                value="{{ old('license_code') }}" autocomplete="off"
                                placeholder="MSI-XXXXXX-XXXXXX-XXXXXX-XXXXXX">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="clear_license_code"
                                    name="clear_license_code" value="1">
                                <label class="form-check-label" for="clear_license_code">Hapus lisensi aktif dari
                                    database</label>
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="revoke_active_license"
                                    name="revoke_active_license" value="1">
                                <label class="form-check-label" for="revoke_active_license">Revoke lisensi aktif (khusus
                                    token
                                    komersial)</label>
                            </div>
                            <small class="form-text text-muted">Total revoked token hashes:
                                {{ $effective['license_revoked_count'] }}</small>
                        </div>

                        <h5 class="font-weight-bold">System & Feature Flags</h5>
                        <div class="form-group">
                            <label>Brand Name (Global)</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['system_brand_name_source'] }}</span>
                            </p>
                            <input type="text" name="system_brand_name" class="form-control"
                                value="{{ old('system_brand_name', $values['system_brand_name']) }}"
                                placeholder="MS ISBN Publishing">
                        </div>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="assistant_enabled" id="assistant_enabled"
                                class="form-check-input" value="1"
                                {{ old('assistant_enabled', $values['assistant_enabled']) ? 'checked' : '' }}>
                            <label for="assistant_enabled" class="form-check-label">Aktifkan AI Assistant</label>
                        </div>
                        <div class="form-group">
                            <label>Assignment Warning Threshold (jam)</label>
                            <input type="number" min="1" max="240" name="assignment_warning_hours"
                                class="form-control"
                                value="{{ old('assignment_warning_hours', $values['assignment_warning_hours']) }}">
                        </div>
                        <div class="form-group mb-4">
                            <label>Assistant Temperature (0 - 1)</label>
                            <input type="number" step="0.01" min="0" max="1"
                                name="assistant_temperature" class="form-control"
                                value="{{ old('assistant_temperature', $values['assistant_temperature']) }}">
                        </div>

                        <h5 class="font-weight-bold">Tracking Order Verification</h5>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="tracking_verification_enabled"
                                id="tracking_verification_enabled" class="form-check-input" value="1"
                                {{ old('tracking_verification_enabled', $values['tracking_verification_enabled']) ? 'checked' : '' }}>
                            <label for="tracking_verification_enabled" class="form-check-label">Aktifkan OTP verifikasi
                                tracking
                                order publik</label>
                        </div>
                        <div class="form-group">
                            <label>Tracking Allowed Channels (comma separated)</label>
                            <input type="text" name="tracking_allowed_channels" class="form-control"
                                value="{{ old('tracking_allowed_channels', $values['tracking_allowed_channels']) }}"
                                placeholder="phone,email,whatsapp">
                        </div>
                        <div class="form-group mb-4">
                            <label>Tracking OTP Expiration (minutes)</label>
                            <input type="number" min="1" max="30" name="tracking_otp_expiration_minutes"
                                class="form-control"
                                value="{{ old('tracking_otp_expiration_minutes', $values['tracking_otp_expiration_minutes']) }}">
                        </div>

                        <h5 class="font-weight-bold">Payment / Shipping API</h5>
                        <div class="form-group">
                            <label>iPaymu API Key</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['ipaymu_api_key_source'] }}</span>
                            </p>
                            <input type="password" name="ipaymu_api_key" class="form-control"
                                value="{{ old('ipaymu_api_key', $values['ipaymu_api_key']) }}">
                        </div>
                        <div class="form-group">
                            <label>iPaymu VA</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source: {{ $effective['ipaymu_va_source'] }}</span>
                            </p>
                            <input type="text" name="ipaymu_va" class="form-control"
                                value="{{ old('ipaymu_va', $values['ipaymu_va']) }}">
                        </div>
                        <div class="form-group">
                            <label>iPaymu Base URL</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['ipaymu_base_url_source'] }}</span>
                            </p>
                            <input type="url" name="ipaymu_base_url" class="form-control"
                                value="{{ old('ipaymu_base_url', $values['ipaymu_base_url']) }}">
                        </div>
                        <div class="form-group form-check mb-3">
                            <input type="checkbox" name="ipaymu_verify_ssl" id="ipaymu_verify_ssl"
                                class="form-check-input" value="1"
                                {{ old('ipaymu_verify_ssl', $values['ipaymu_verify_ssl']) ? 'checked' : '' }}>
                            <label for="ipaymu_verify_ssl" class="form-check-label">Verifikasi SSL iPaymu</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['ipaymu_verify_ssl_source'] }}</span>
                        </div>
                        <div class="form-group">
                            <label>RajaOngkir API Key</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['rajaongkir_key_source'] }}</span>
                            </p>
                            <input type="text" name="rajaongkir_key" class="form-control"
                                value="{{ old('rajaongkir_key', $values['rajaongkir_key']) }}">
                        </div>
                        <div class="form-group">
                            <label>RajaOngkir Origin City ID</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['rajaongkir_origin_city_id_source'] }}</span>
                            </p>
                            <input type="text" name="rajaongkir_origin_city_id" class="form-control"
                                value="{{ old('rajaongkir_origin_city_id', $values['rajaongkir_origin_city_id']) }}">
                        </div>
                        <div class="form-group form-check mb-4">
                            <input type="checkbox" name="rajaongkir_verify_ssl" id="rajaongkir_verify_ssl"
                                class="form-check-input" value="1"
                                {{ old('rajaongkir_verify_ssl', $values['rajaongkir_verify_ssl']) ? 'checked' : '' }}>
                            <label for="rajaongkir_verify_ssl" class="form-check-label">Verifikasi SSL RajaOngkir</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['rajaongkir_verify_ssl_source'] }}</span>
                        </div>

                        <h5 class="font-weight-bold">Perpusnas Integration</h5>
                        <div class="form-group">
                            <label>Perpusnas Base URL</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['perpusnas_base_url_source'] }}</span>
                            </p>
                            <input type="url" name="perpusnas_base_url" class="form-control"
                                value="{{ old('perpusnas_base_url', $values['perpusnas_base_url']) }}">
                        </div>
                        <div class="form-group">
                            <label>Perpusnas Token</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['perpusnas_token_source'] }}</span>
                            </p>
                            <input type="password" name="perpusnas_token" class="form-control"
                                value="{{ old('perpusnas_token', $values['perpusnas_token']) }}">
                        </div>
                        <div class="form-group">
                            <label>Perpusnas Username</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['perpusnas_username_source'] }}</span>
                            </p>
                            <input type="text" name="perpusnas_username" class="form-control"
                                value="{{ old('perpusnas_username', $values['perpusnas_username']) }}">
                        </div>
                        <div class="form-group">
                            <label>Perpusnas Password</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['perpusnas_password_source'] }}</span>
                            </p>
                            <input type="password" name="perpusnas_password" class="form-control"
                                value="{{ old('perpusnas_password', $values['perpusnas_password']) }}">
                        </div>
                        <div class="form-group form-check mb-4">
                            <input type="checkbox" name="perpusnas_verify_ssl" id="perpusnas_verify_ssl"
                                class="form-check-input" value="1"
                                {{ old('perpusnas_verify_ssl', $values['perpusnas_verify_ssl']) ? 'checked' : '' }}>
                            <label for="perpusnas_verify_ssl" class="form-check-label">Verifikasi SSL Perpusnas</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['perpusnas_verify_ssl_source'] }}</span>
                        </div>

                        <h5 class="font-weight-bold">Reminder Channels</h5>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="reminder_email_enabled" id="reminder_email_enabled"
                                class="form-check-input" value="1"
                                {{ old('reminder_email_enabled', $values['reminder_email_enabled']) ? 'checked' : '' }}>
                            <label for="reminder_email_enabled" class="form-check-label">Enable Reminder Email</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['reminder_email_enabled_source'] }}</span>
                        </div>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="reminder_whatsapp_enabled" id="reminder_whatsapp_enabled"
                                class="form-check-input" value="1"
                                {{ old('reminder_whatsapp_enabled', $values['reminder_whatsapp_enabled']) ? 'checked' : '' }}>
                            <label for="reminder_whatsapp_enabled" class="form-check-label">Enable Reminder
                                WhatsApp</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['reminder_whatsapp_enabled_source'] }}</span>
                        </div>
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" name="reminder_sms_enabled" id="reminder_sms_enabled"
                                class="form-check-input" value="1"
                                {{ old('reminder_sms_enabled', $values['reminder_sms_enabled']) ? 'checked' : '' }}>
                            <label for="reminder_sms_enabled" class="form-check-label">Enable Reminder SMS</label>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['reminder_sms_enabled_source'] }}</span>
                        </div>
                        <div class="form-group">
                            <label>Reminder WhatsApp Webhook URL</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['reminder_whatsapp_webhook_url_source'] }}</span>
                            </p>
                            <input type="url" name="reminder_whatsapp_webhook_url" class="form-control"
                                value="{{ old('reminder_whatsapp_webhook_url', $values['reminder_whatsapp_webhook_url']) }}">
                        </div>
                        <div class="form-group mb-4">
                            <label>Reminder SMS Webhook URL</label>
                            <p class="text-muted small mb-1">
                                <span class="badge badge-secondary">source:
                                    {{ $effective['reminder_sms_webhook_url_source'] }}</span>
                            </p>
                            <input type="url" name="reminder_sms_webhook_url" class="form-control"
                                value="{{ old('reminder_sms_webhook_url', $values['reminder_sms_webhook_url']) }}">
                        </div>

                        <h5 class="font-weight-bold">Assistant Provider: OpenRouter</h5>
                        <p class="text-muted small mb-2">
                            Effective Key: <strong>{{ $effective['openrouter_api_key'] }}</strong>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['openrouter_api_key_source'] }}</span>
                        </p>
                        <div class="form-group">
                            <label>OpenRouter API Key</label>
                            <input type="password" name="openrouter_api_key" class="form-control"
                                value="{{ old('openrouter_api_key') }}" autocomplete="off"
                                placeholder="Isi hanya jika ingin mengganti key">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="clear_openrouter_api_key"
                                    name="clear_openrouter_api_key" value="1">
                                <label class="form-check-label" for="clear_openrouter_api_key">Hapus key OpenRouter dari
                                    database (kembali ke .env)</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>OpenRouter Model</label>
                            <input type="text" name="openrouter_model" class="form-control"
                                value="{{ old('openrouter_model', $values['openrouter_model']) }}"
                                placeholder="meta-llama/llama-3.1-8b-instruct:free">
                        </div>
                        <div class="form-group">
                            <label>OpenRouter Base URL</label>
                            <input type="url" name="openrouter_base_url" class="form-control"
                                value="{{ old('openrouter_base_url', $values['openrouter_base_url']) }}"
                                placeholder="https://openrouter.ai/api/v1">
                        </div>
                        <div class="form-group form-check mb-4">
                            <input type="checkbox" name="openrouter_verify_ssl" id="openrouter_verify_ssl"
                                class="form-check-input" value="1"
                                {{ old('openrouter_verify_ssl', $values['openrouter_verify_ssl']) ? 'checked' : '' }}>
                            <label for="openrouter_verify_ssl" class="form-check-label">Verifikasi SSL OpenRouter</label>
                        </div>

                        <h5 class="font-weight-bold">Assistant Fallback: OpenAI (Opsional)</h5>
                        <p class="text-muted small mb-2">
                            Effective Key: <strong>{{ $effective['openai_api_key'] }}</strong>
                            <span class="ml-2 badge badge-secondary">source:
                                {{ $effective['openai_api_key_source'] }}</span>
                        </p>
                        <div class="form-group">
                            <label>OpenAI API Key</label>
                            <input type="password" name="openai_api_key" class="form-control"
                                value="{{ old('openai_api_key') }}" autocomplete="off"
                                placeholder="Isi hanya jika ingin mengganti key">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="clear_openai_api_key"
                                    name="clear_openai_api_key" value="1">
                                <label class="form-check-label" for="clear_openai_api_key">Hapus key OpenAI dari database
                                    (kembali ke .env)</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>OpenAI Model</label>
                            <input type="text" name="openai_model" class="form-control"
                                value="{{ old('openai_model', $values['openai_model']) }}" placeholder="gpt-4o-mini">
                        </div>
                        <div class="form-group">
                            <label>OpenAI Base URL</label>
                            <input type="url" name="openai_base_url" class="form-control"
                                value="{{ old('openai_base_url', $values['openai_base_url']) }}"
                                placeholder="https://api.openai.com/v1">
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" name="openai_verify_ssl" id="openai_verify_ssl"
                                class="form-check-input" value="1"
                                {{ old('openai_verify_ssl', $values['openai_verify_ssl']) ? 'checked' : '' }}>
                            <label for="openai_verify_ssl" class="form-check-label">Verifikasi SSL OpenAI</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan Settings
                </button>
            </div>
        </form>
    </div>

    <div class="card card-outline card-secondary mt-3">
        <div class="card-header">
            <h3 class="card-title">Audit Log Perubahan Settings (20 terbaru)</h3>
        </div>
        <div class="card-body border-bottom pb-0">
            <form method="GET" class="row">
                <div class="col-md-4 form-group">
                    <label>Filter Key</label>
                    <input type="text" name="audit_key" value="{{ $filters['audit_key'] ?? '' }}"
                        class="form-control" placeholder="assistant.openrouter_api_key">
                </div>
                <div class="col-md-3 form-group">
                    <label>Filter Changed By (user id)</label>
                    <input type="number" name="audit_actor" value="{{ $filters['audit_actor'] ?? '' }}"
                        class="form-control" placeholder="1">
                </div>
                <div class="col-md-5 form-group d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary mr-2">Filter</button>
                    <a href="{{ route('settings.system.audit.export', ['audit_key' => $filters['audit_key'] ?? null, 'audit_actor' => $filters['audit_actor'] ?? null]) }}"
                        class="btn btn-outline-success mr-2">Export CSV</a>
                    <a href="{{ route('settings.system.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Key</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                            <th>Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ optional($audit->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td><code>{{ $audit->key }}</code></td>
                                <td>{{ $audit->old_value ?? '-' }}</td>
                                <td>{{ $audit->new_value ?? '-' }}</td>
                                <td>{{ $audit->changed_by ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Belum ada perubahan settings.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
