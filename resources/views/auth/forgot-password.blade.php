<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Forgot Password</h2>
        <p class="text-sm text-slate-500 mt-1">
            Masukkan email akun Anda. Kami akan kirim tautan untuk reset password.
        </p>
    </div>

    <x-auth-session-status class="auth-alert" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="field-label">Email</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required
                autofocus placeholder="nama@domain.com">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="cta-btn">Kirim Link Reset Password</button>
    </form>
</x-guest-layout>
