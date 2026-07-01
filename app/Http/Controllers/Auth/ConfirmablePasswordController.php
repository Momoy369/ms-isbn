<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (
            !Auth::guard('web')->validate([
                'email' => $request->user()->email,
                'password' => $request->password,
            ])
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $role = (string) $request->user()->role;
        $target = $this->resolveRedirectTarget($role);

        return $this->redirectToIntendedOrTarget($request, $role, $target);
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
            return route('store.index', absolute: false);
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
