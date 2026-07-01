<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Reset Password</h2>
        <p class="text-sm text-slate-500 mt-1">Buat password baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" class="field-input" type="email" name="email"
                value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                placeholder="nama@domain.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="field-label">Password Baru</label>
            <div class="password-wrap">
                <input id="password" class="field-input" type="password" name="password" required
                    autocomplete="new-password" placeholder="Minimal 8 karakter" data-password-strength
                    data-strength-target="reset-password-strength" data-password-rules-target="reset-password-rules">
                <button type="button" class="password-toggle" data-toggle-password data-target="password"
                    aria-label="Tampilkan password baru">Tampilkan</button>
            </div>
            <div id="reset-password-strength" class="password-strength" aria-live="polite">
                <div class="password-strength-track">
                    <div class="password-strength-fill" data-strength-fill></div>
                </div>
                <div class="password-strength-text" data-strength-text>Kekuatan password: Belum diisi</div>
            </div>
            <ul id="reset-password-rules" class="password-rules">
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
            <label for="password_confirmation" class="field-label">Konfirmasi Password Baru</label>
            <div class="password-wrap">
                <input id="password_confirmation" class="field-input" type="password" name="password_confirmation"
                    required autocomplete="new-password" placeholder="Ulangi password baru">
                <button type="button" class="password-toggle" data-toggle-password data-target="password_confirmation"
                    aria-label="Tampilkan konfirmasi password baru">Tampilkan</button>
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="cta-btn">Simpan Password Baru</button>
    </form>
</x-guest-layout>
