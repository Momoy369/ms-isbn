<x-guest-layout>
    @php
        $isStoreFlow = request('from') === 'store';
    @endphp

    @if ($isStoreFlow)
        <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Login Customer Storefront untuk melanjutkan belanja atau cek status pesanan.
        </div>
    @endif

    @if (session('status'))
        <div class="auth-alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Sign In</h2>
        <p class="text-sm text-slate-500 mt-1">Gunakan akun terdaftar untuk masuk ke MS Publishing.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="return_to" value="{{ request('return_to') }}">

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required
                autofocus autocomplete="username" placeholder="nama@domain.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Password</label>
            <div class="password-wrap">
                <input id="password" class="field-input" type="password" name="password" required
                    autocomplete="current-password" placeholder="Masukkan password">
                <button type="button" class="password-toggle" data-toggle-password data-target="password"
                    aria-label="Tampilkan password">Tampilkan</button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm pt-1">
            <label for="remember_me" class="inline-flex items-center text-slate-600">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-slate-300 text-teal-700 shadow-sm focus:ring-teal-500">
                <span class="ms-2">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="cta-btn">
            Log in to MS Publishing
        </button>

        <div class="pt-2 text-sm text-slate-600">
            @if (Route::has('register'))
                Belum punya akun?
                <a class="auth-link"
                    href="{{ route('register', ['from' => request('from'), 'return_to' => request('return_to')]) }}">
                    Daftar sekarang
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
