<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installer | {{ $appName }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --soft: #334155;
            --line: #dbe2ea;
            --ok: #059669;
            --bad: #dc2626;
            --bg1: #f8fafc;
            --bg2: #e0f2fe;
            --bg3: #fef3c7;
            --card: rgba(255, 255, 255, .92);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 15%, var(--bg3), transparent 35%),
                radial-gradient(circle at 90% 80%, var(--bg2), transparent 45%),
                linear-gradient(140deg, var(--bg1), #fff);
            min-height: 100vh;
        }

        .shell {
            max-width: 1040px;
            margin: 0 auto;
            padding: 30px 18px 50px;
        }

        .hero {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--card);
            box-shadow: 0 20px 55px rgba(15, 23, 42, .10);
            padding: 22px;
            margin-bottom: 18px;
        }

        .title {
            margin: 0 0 8px;
            font-size: 32px;
            line-height: 1.15;
        }

        .subtitle {
            margin: 0;
            color: var(--soft);
            line-height: 1.5;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
            margin-bottom: 14px;
        }

        .badge-ok {
            background: rgba(5, 150, 105, .14);
            color: var(--ok);
            border: 1px solid rgba(5, 150, 105, .35);
        }

        .badge-bad {
            background: rgba(220, 38, 38, .12);
            color: var(--bad);
            border: 1px solid rgba(220, 38, 38, .32);
        }

        .grid {
            display: grid;
            gap: 18px;
            grid-template-columns: 1.2fr .8fr;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--card);
            box-shadow: 0 12px 36px rgba(15, 23, 42, .07);
            overflow: hidden;
        }

        .card h2 {
            margin: 0;
            font-size: 18px;
        }

        .card-head {
            border-bottom: 1px solid var(--line);
            padding: 14px 16px;
            background: rgba(148, 163, 184, .08);
        }

        .card-body {
            padding: 12px 16px 16px;
        }

        .req-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .req-row:last-child {
            border-bottom: 0;
        }

        .req-left {
            min-width: 0;
        }

        .req-label {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .req-value {
            color: var(--soft);
            font-size: 13px;
        }

        .state {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .state-ok {
            background: rgba(5, 150, 105, .14);
            color: var(--ok);
            border: 1px solid rgba(5, 150, 105, .35);
        }

        .state-bad {
            background: rgba(220, 38, 38, .12);
            color: var(--bad);
            border: 1px solid rgba(220, 38, 38, .32);
        }

        ol {
            margin: 0;
            padding-left: 20px;
        }

        li {
            margin: 8px 0;
            line-height: 1.4;
        }

        code {
            background: #0f172a;
            color: #e2e8f0;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .title {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <section class="hero">
            <div class="badge {{ $allOk ? 'badge-ok' : 'badge-bad' }}">
                {{ $allOk ? 'Environment Siap' : 'Perlu Perbaikan Environment' }}
            </div>
            <h1 class="title">Installer {{ $appName }}</h1>
            <p class="subtitle">Halaman ini membantu verifikasi environment sebelum aplikasi dipakai. Jika masih merah,
                selesaikan
                requirement di bawah lalu jalankan langkah instalasi.</p>
        </section>

        <section class="grid">
            <article class="card">
                <div class="card-head">
                    <h2>Checklist Environment</h2>
                </div>
                <div class="card-body">
                    @foreach ($requirements as $req)
                        <div class="req-row">
                            <div class="req-left">
                                <div class="req-label">{{ $req['label'] }}</div>
                                <div class="req-value">{{ $req['value'] }}</div>
                            </div>
                            <span class="state {{ $req['ok'] ? 'state-ok' : 'state-bad' }}">
                                {{ $req['ok'] ? 'OK' : 'FAIL' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="card">
                <div class="card-head">
                    <h2>Langkah Instalasi</h2>
                </div>
                <div class="card-body">
                    <ol>
                        @foreach ($steps as $step)
                            <li><code>{{ $step }}</code></li>
                        @endforeach
                    </ol>
                </div>
            </article>
        </section>
    </div>
</body>

</html>
