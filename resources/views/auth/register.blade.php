<x-guest-layout>
    @php
        $isStoreFlow = request('from') === 'store';
    @endphp

    @if ($isStoreFlow)
        <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Daftar Customer Storefront untuk melanjutkan checkout dan pelacakan pesanan.
        </div>
    @endif

    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Create Account</h2>
        <p class="text-sm text-slate-500 mt-1">Daftarkan akun baru untuk mengakses portal MS Publishing.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="return_to" value="{{ request('return_to') }}">

        <div>
            <label for="name" class="field-label">Nama</label>
            <input id="name" class="field-input" type="text" name="name" value="{{ old('name') }}" required
                autofocus autocomplete="name" placeholder="Nama lengkap">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required
                autocomplete="username" placeholder="nama@domain.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Password</label>
            <div class="password-wrap">
                <input id="password" class="field-input" type="password" name="password" required
                    autocomplete="new-password" placeholder="Minimal 8 karakter" data-password-strength
                    data-strength-target="register-password-strength"
                    data-password-rules-target="register-password-rules">
                <button type="button" class="password-toggle" data-toggle-password data-target="password"
                    aria-label="Tampilkan password">Tampilkan</button>
            </div>
            <div id="register-password-strength" class="password-strength" aria-live="polite">
                <div class="password-strength-track">
                    <div class="password-strength-fill" data-strength-fill></div>
                </div>
                <div class="password-strength-text" data-strength-text>Kekuatan password: Belum diisi</div>
            </div>
            <ul id="register-password-rules" class="password-rules">
                <li class="password-rule" data-rule="min8">Minimal 8 karakter</li>
                <li class="password-rule" data-rule="upper">Mengandung huruf besar (A-Z)</li>
                <li class="password-rule" data-rule="lower">Mengandung huruf kecil (a-z)</li>
                <li class="password-rule" data-rule="digit">Mengandung angka (0-9)</li>
                <li class="password-rule" data-rule="symbol">Mengandung simbol (contoh: !@#$)</li>
            </ul>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Konfirmasi Password</label>
            <div class="password-wrap">
                <input id="password_confirmation" class="field-input" type="password" name="password_confirmation"
                    required autocomplete="new-password" placeholder="Ulangi password">
                <button type="button" class="password-toggle" data-toggle-password data-target="password_confirmation"
                    aria-label="Tampilkan konfirmasi password">Tampilkan</button>
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="cta-btn">Daftar Akun</button>

        <div class="text-sm text-slate-600">
            Sudah punya akun?
            <a class="auth-link"
                href="{{ route('login', ['from' => request('from'), 'return_to' => request('return_to')]) }}">
                Login di sini
            </a>
        </div>
    </form>
</x-guest-layout>
