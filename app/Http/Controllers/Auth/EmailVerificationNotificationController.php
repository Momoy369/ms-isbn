<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $role = (string) $request->user()->role;
        $target = $this->resolveRedirectTarget($role);

        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectToIntendedOrTarget($request, $role, $target);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    private function redirectToIntendedOrTarget(Request $request, string $role, string $target): RedirectResponse
    {
        if ($role === 'author' && $this->isDashboardIntended($request)) {
            $request->session()->forget('url.intended');

            return redirect()->to($target);
        }

        return redirect()->intended($target);
    }

    private function isDashboardIntended(Request $request): bool
    {
        $intended = (string) $request->session()->get('url.intended', '');

        if ($intended === '') {
            return false;
        }

        $intendedPath = parse_url($intended, PHP_URL_PATH);
        $dashboardPath = parse_url(route('dashboard', absolute: false), PHP_URL_PATH);

        return is_string($intendedPath)
            && is_string($dashboardPath)
            && $intendedPath === $dashboardPath;
    }

    private function resolveRedirectTarget(string $role): string
    {
        if (in_array($role, ['customer', 'reader'], true)) {
            return route('customer.dashboard', absolute: false);
        }

        if ($role === 'author') {
            return route('author.dashboard', absolute: false);
        }

        if (in_array($role, ['editor', 'layouter', 'designer'], true)) {
            return route('assignments.my', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
