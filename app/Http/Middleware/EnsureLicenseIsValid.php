<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use App\Services\SystemSettingService;
use Closure;
use Illuminate\Http\Request;

class EnsureLicenseIsValid
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->runningInConsole() || app()->runningUnitTests()) {
            return $next($request);
        }

        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        $required = true;
        $storedCode = '';

        try {
            /** @var SystemSettingService $settings */
            $settings = app(SystemSettingService::class);
            $required = $settings->getBool('license.required', true);
            $storedCode = (string) $settings->get('license.code', '');
        } catch (\Throwable $e) {
            $required = true;
            $storedCode = '';
        }

        if (!$required) {
            return $next($request);
        }

        /** @var LicenseService $license */
        $license = app(LicenseService::class);

        if ($license->isLicenseValid($storedCode)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lisensi sistem belum valid. Hubungi owner/superadmin untuk aktivasi.',
            ], 423);
        }

        if (auth()->check() && in_array(auth()->user()->role, ['owner', 'superadmin'], true)) {
            return redirect()
                ->route('settings.system.index')
                ->with('error', 'Lisensi sistem belum valid. Masukkan kode lisensi owner terlebih dahulu.');
        }

        abort(423, 'Lisensi sistem belum valid.');
    }

    private function isExemptRoute(Request $request): bool
    {
        if ($request->is('up')) {
            return true;
        }

        if ($request->routeIs('login', 'logout', 'password.*', 'verification.*', 'settings.system.*')) {
            return true;
        }

        if ($request->is('storage/*') || $request->is('build/*') || $request->is('vendor/*')) {
            return true;
        }

        return false;
    }
}
