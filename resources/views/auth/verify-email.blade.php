<x-guest-layout>
    <div class="mb-5">
        <h2 class="text-2xl font-extrabold text-slate-900">Verifikasi Email</h2>
        <p class="text-sm text-slate-500 mt-1">
            Kami sudah mengirim tautan verifikasi ke email Anda. Klik tautan tersebut sebelum melanjutkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            Link verifikasi baru sudah dikirim ke email Anda.
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit" class="cta-btn" style="width:auto; padding:10px 14px;">
                    Kirim Ulang Email Verifikasi
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="auth-link text-sm">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
