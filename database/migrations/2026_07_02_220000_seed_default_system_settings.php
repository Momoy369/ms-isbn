<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        $defaults = [
            ['key' => 'system.brand_name', 'value' => 'MS ISBN Publishing', 'is_encrypted' => false, 'description' => 'Brand sistem global'],
            ['key' => 'license.required', 'value' => '1', 'is_encrypted' => false, 'description' => 'Wajib lisensi untuk akses sistem'],
            ['key' => 'license.plan', 'value' => 'standard', 'is_encrypted' => false, 'description' => 'Plan lisensi aktif'],
            ['key' => 'license.trial', 'value' => '0', 'is_encrypted' => false, 'description' => 'Status trial lisensi'],
            ['key' => 'license.revoked_hashes', 'value' => '[]', 'is_encrypted' => false, 'description' => 'Hash lisensi yang sudah direvoke'],
            ['key' => 'feature.assistant_enabled', 'value' => '1', 'is_encrypted' => false, 'description' => 'Toggle AI assistant'],
            ['key' => 'workflow.assignment_warning_hours', 'value' => '24', 'is_encrypted' => false, 'description' => 'Ambang warning assignment (jam)'],
            ['key' => 'assistant.temperature', 'value' => '0.2', 'is_encrypted' => false, 'description' => 'Temperature AI assistant'],
            ['key' => 'tracking.verification_enabled', 'value' => '1', 'is_encrypted' => false, 'description' => 'Aktifkan verifikasi tracking order publik'],
            ['key' => 'tracking.allowed_channels', 'value' => 'phone,email,whatsapp', 'is_encrypted' => false, 'description' => 'Channel OTP tracking order'],
            ['key' => 'tracking.otp_expiration_minutes', 'value' => '10', 'is_encrypted' => false, 'description' => 'Masa berlaku OTP tracking (menit)'],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('system_settings')->where('key', $row['key'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('system_settings')->insert([
                'key' => $row['key'],
                'value' => $row['value'],
                'is_encrypted' => $row['is_encrypted'],
                'description' => $row['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'system.brand_name',
            'license.required',
            'license.plan',
            'license.trial',
            'license.revoked_hashes',
            'feature.assistant_enabled',
            'workflow.assignment_warning_hours',
            'assistant.temperature',
            'tracking.verification_enabled',
            'tracking.allowed_channels',
            'tracking.otp_expiration_minutes',
        ])->delete();
    }
};
