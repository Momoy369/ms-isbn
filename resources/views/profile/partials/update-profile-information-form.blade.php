<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        @if (session('warning'))
            <div class="rounded-md bg-yellow-100 text-yellow-900 px-3 py-2 text-sm">
                {{ session('warning') }}
            </div>
        @endif

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)"
                autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                rows="3">{{ old('address', $user->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        @if (in_array($user->role, ['customer', 'reader'], true))
            @if (session('success'))
                <div class="rounded-md bg-green-100 text-green-900 px-3 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (isset($latestUpgradeRequest) && $latestUpgradeRequest)
                <div class="rounded-md border px-3 py-2 text-sm">
                    <p class="font-semibold mb-1">Status Pengajuan Upgrade Author</p>
                    <span
                        class="inline-flex px-2 py-1 rounded text-white text-xs {{ $latestUpgradeRequest->status === 'approved' ? 'bg-green-600' : ($latestUpgradeRequest->status === 'rejected' ? 'bg-red-600' : 'bg-yellow-600') }}">
                        {{ strtoupper($latestUpgradeRequest->status) }}
                    </span>
                    @if ($latestUpgradeRequest->review_notes)
                        <p class="mt-2 text-gray-700"><strong>Catatan Admin:</strong>
                            {{ $latestUpgradeRequest->review_notes }}</p>
                    @endif
                </div>
            @endif

            <div class="rounded-md border px-3 py-2 text-sm">
                <p class="font-semibold mb-2">Checklist Data Author</p>
                <p class="text-gray-600 mb-2">Lengkapi data berikut untuk pengajuan upgrade role ke author.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="ktp_number" :value="__('Nomor KTP')" />
                        <x-text-input id="ktp_number" name="ktp_number" type="text" class="mt-1 block w-full"
                            :value="old('ktp_number', $user->ktp_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('ktp_number')" />
                    </div>
                    <div>
                        <x-input-label for="ktp_name" :value="__('Nama Sesuai KTP')" />
                        <x-text-input id="ktp_name" name="ktp_name" type="text" class="mt-1 block w-full"
                            :value="old('ktp_name', $user->ktp_name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('ktp_name')" />
                    </div>
                    <div>
                        <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                        <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full"
                            :value="old('birth_date', optional($user->birth_date)->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
                    </div>
                    <div>
                        <x-input-label for="bank_name" :value="__('Nama Bank')" />
                        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full"
                            :value="old('bank_name', $user->bank_name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                    </div>
                    <div>
                        <x-input-label for="bank_account_number" :value="__('Nomor Rekening')" />
                        <x-text-input id="bank_account_number" name="bank_account_number" type="text"
                            class="mt-1 block w-full" :value="old('bank_account_number', $user->bank_account_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('bank_account_number')" />
                    </div>
                    <div>
                        <x-input-label for="bank_account_holder" :value="__('Nama Pemilik Rekening')" />
                        <x-text-input id="bank_account_holder" name="bank_account_holder" type="text"
                            class="mt-1 block w-full" :value="old('bank_account_holder', $user->bank_account_holder)" />
                        <x-input-error class="mt-2" :messages="$errors->get('bank_account_holder')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="bank_branch" :value="__('Cabang Bank (Opsional)')" />
                        <x-text-input id="bank_branch" name="bank_branch" type="text" class="mt-1 block w-full"
                            :value="old('bank_branch', $user->bank_branch)" />
                        <x-input-error class="mt-2" :messages="$errors->get('bank_branch')" />
                    </div>
                </div>
            </div>

            <div>
                <x-input-label for="author_upgrade_note" :value="__('Catatan Pengajuan Upgrade (Opsional)')" />
                <textarea id="author_upgrade_note" name="author_upgrade_note"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    rows="3">{{ old('author_upgrade_note') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('author_upgrade_note')" />
            </div>

            <div>
                <x-input-label for="author_upgrade_document" :value="__('Lampiran Dokumen (PDF/JPG/PNG, opsional)')" />
                <input id="author_upgrade_document" name="author_upgrade_document" type="file"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <x-input-error class="mt-2" :messages="$errors->get('author_upgrade_document')" />

                @if (isset($latestUpgradeRequest) && $latestUpgradeRequest && $latestUpgradeRequest->supporting_document_path)
                    <p class="mt-2 text-xs text-gray-600">
                        Dokumen terakhir sudah tersimpan dan dapat direview admin.
                    </p>
                @endif
            </div>

            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="upgrade_to_author" value="0">
                <input type="checkbox" name="upgrade_to_author" value="1"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Ajukan upgrade akun ini menjadi Author (butuh approval
                    admin)</span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('upgrade_to_author')" />

            <div class="rounded-md bg-gray-50 border px-3 py-2 text-xs text-gray-700">
                <p class="font-semibold mb-1">SOP Mini Pengajuan Author</p>
                <ol class="list-decimal pl-5 space-y-1">
                    <li>Lengkapi checklist data author (KTP, kontak, tanggal lahir, data bank).</li>
                    <li>Simpan profile, lalu centang pengajuan upgrade author.</li>
                    <li>Tunggu review admin pada menu Review Upgrade Author.</li>
                    <li>Jika ditolak, perbaiki data sesuai catatan admin lalu ajukan ulang.</li>
                </ol>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
