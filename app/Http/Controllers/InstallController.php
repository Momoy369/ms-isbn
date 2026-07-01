<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;

class InstallController extends Controller
{
    public function index()
    {
        $requirements = [
            [
                'label' => 'PHP >= 8.2',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
            ],
            [
                'label' => 'Ekstensi pdo_mysql',
                'ok' => extension_loaded('pdo_mysql'),
                'value' => extension_loaded('pdo_mysql') ? 'terpasang' : 'belum terpasang',
            ],
            [
                'label' => 'Ekstensi mbstring',
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'terpasang' : 'belum terpasang',
            ],
            [
                'label' => 'Ekstensi openssl',
                'ok' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'terpasang' : 'belum terpasang',
            ],
            [
                'label' => 'Ekstensi zip',
                'ok' => extension_loaded('zip'),
                'value' => extension_loaded('zip') ? 'terpasang' : 'belum terpasang',
            ],
            [
                'label' => 'APP_KEY tersedia',
                'ok' => trim((string) config('app.key', '')) !== '',
                'value' => trim((string) config('app.key', '')) !== '' ? 'sudah ada' : 'kosong',
            ],
            [
                'label' => 'Koneksi DB + tabel users',
                'ok' => $this->hasUsersTable(),
                'value' => $this->hasUsersTable() ? 'terhubung' : 'belum siap',
            ],
        ];

        $allOk = collect($requirements)->every(fn(array $item): bool => $item['ok'] === true);

        $steps = [
            'cp .env.example .env',
            'composer install',
            'php artisan key:generate',
            'php artisan migrate --force',
            'npm install',
            'npm run build',
        ];

        return view('install.index', [
            'requirements' => $requirements,
            'allOk' => $allOk,
            'steps' => $steps,
            'appName' => config('app.name', 'MS ISBN Publishing'),
        ]);
    }

    private function hasUsersTable(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
