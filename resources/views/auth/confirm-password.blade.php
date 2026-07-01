<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Konfirmasi Password</h2>
        <p class="text-sm text-slate-500 mt-1">
            Ini area aman. Konfirmasi password Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="field-label">Password</label>
            <div class="password-wrap">
                <input id="password" class="field-input" type="password" name="password" required
                    autocomplete="current-password" placeholder="Masukkan password Anda">
                <button type="button" class="password-toggle" data-toggle-password data-target="password"
                    aria-label="Tampilkan password">Tampilkan</button>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="cta-btn">Konfirmasi</button>
    </form>
</x-guest-layout>
