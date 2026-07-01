<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan - MS ISBN</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f6f8fb;
            color: #1f2937;
        }

        .wrap {
            width: min(680px, 92vw);
            margin: 5vh auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 1.2rem;
        }

        .brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .brand img {
            max-height: 48px;
        }

        input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: .75rem .8rem;
            margin: .4rem 0 .8rem;
        }

        button {
            background: #005f73;
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: .7rem 1rem;
            font-weight: 700;
            cursor: pointer;
        }

        .alert {
            border-radius: 10px;
            padding: .65rem .8rem;
            margin-bottom: .8rem;
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }

        a {
            color: #005f73;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="brand">
            <img src="{{ asset('logowide.png') }}" alt="MS ISBN">
            <a href="{{ route('store.index') }}">Kembali ke Store</a>
        </div>

        <h2 style="margin:.2rem 0 .5rem;">Lacak Pesanan Anda</h2>
        <p style="margin-top:0; color:#64748b;">Masukkan nomor order, contoh: SO-20260625101010-123</p>

        @if (session('danger'))
            <div class="alert">{{ session('danger') }}</div>
        @endif
        @if (session('success'))
            <div class="alert" style="background:#dcfce7;border-color:#86efac;">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('store.track.lookup') }}">
            @csrf
            <label for="order_number">Nomor Order</label>
            <input id="order_number" name="order_number" type="text" value="{{ old('order_number') }}" required>

            @if (!empty($trackingVerificationEnabled))
                <label for="verification_channel">Channel Verifikasi</label>
                <select id="verification_channel" name="verification_channel" required
                    style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:.75rem .8rem;margin:.4rem 0 .8rem;">
                    @foreach ($allowedChannels as $ch)
                        <option value="{{ $ch }}" {{ old('verification_channel') === $ch ? 'selected' : '' }}>
                            {{ strtoupper($ch) }}
                        </option>
                    @endforeach
                </select>

                <label for="verification_contact">Kontak Verifikasi (email / nomor telepon)</label>
                <input id="verification_contact" name="verification_contact" type="text"
                    value="{{ old('verification_contact') }}" required>
            @endif

            <button type="submit">Cari Order</button>
        </form>

        @if (!empty($challengeId) && !empty($challengeOrderNumber))
            <hr style="border:none;border-top:1px solid #e5e7eb;margin:1rem 0;">
            <h3 style="margin:.2rem 0 .5rem;">Verifikasi OTP</h3>
            <p style="margin-top:0; color:#64748b;">Masukkan OTP yang sudah dikirim untuk order
                <strong>{{ $challengeOrderNumber }}</strong>.
            </p>

            <form method="POST" action="{{ route('store.track.verify') }}">
                @csrf
                <input type="hidden" name="order_number" value="{{ $challengeOrderNumber }}">
                <input type="hidden" name="challenge_id" value="{{ $challengeId }}">
                <label for="otp">Kode OTP</label>
                <input id="otp" name="otp" type="text" maxlength="12" required>
                <button type="submit">Verifikasi & Lihat Order</button>
            </form>
        @endif
    </div>
</body>

</html>
