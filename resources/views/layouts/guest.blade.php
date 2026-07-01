<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MS Publishing') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --ms-ink: #0f172a;
            --ms-soft-ink: #334155;
            --ms-accent: #0f766e;
            --ms-accent-deep: #115e59;
            --ms-surface: #ffffff;
            --ms-border: #e2e8f0;
        }

        body {
            font-family: 'Manrope', sans-serif;
            color: var(--ms-ink);
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 15%, rgba(245, 158, 11, .22), transparent 42%),
                radial-gradient(circle at 85% 85%, rgba(20, 184, 166, .20), transparent 44%),
                linear-gradient(135deg, #f8fafc 0%, #fef3c7 55%, #ecfeff 100%);
        }

        .publishing-grid {
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .24;
            background-image:
                linear-gradient(to right, rgba(148, 163, 184, .16) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148, 163, 184, .16) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .8), transparent);
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            letter-spacing: .2px;
        }

        .auth-shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 1040px;
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            overflow: hidden;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 32px 80px rgba(15, 23, 42, .16);
            backdrop-filter: blur(3px);
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            animation: cardIn .55s cubic-bezier(.22, 1, .36, 1) both;
        }

        .auth-brand {
            padding: 44px;
            background:
                linear-gradient(165deg, rgba(255, 247, 237, .95), rgba(236, 253, 245, .88));
            border-right: 1px solid var(--ms-border);
            animation: panelInLeft .65s cubic-bezier(.22, 1, .36, 1) both;
        }

        .auth-panel {
            padding: 40px;
            background: var(--ms-surface);
            animation: panelInRight .65s cubic-bezier(.22, 1, .36, 1) both;
        }

        .panel-stagger>* {
            opacity: 0;
            transform: translateY(10px);
            animation: contentIn .45s ease-out forwards;
        }

        .panel-stagger>*:nth-child(1) {
            animation-delay: .06s;
        }

        .panel-stagger>*:nth-child(2) {
            animation-delay: .12s;
        }

        .panel-stagger>*:nth-child(3) {
            animation-delay: .18s;
        }

        .panel-stagger>*:nth-child(4) {
            animation-delay: .24s;
        }

        .panel-stagger>*:nth-child(5) {
            animation-delay: .30s;
        }

        .cover-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(15, 118, 110, .1);
            color: var(--ms-accent-deep);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .brand-kpi {
            margin-top: 26px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .kpi-box {
            border: 1px solid var(--ms-border);
            border-radius: 14px;
            padding: 12px;
            background: rgba(255, 255, 255, .8);
        }

        .kpi-box strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            color: var(--ms-accent-deep);
        }

        .kpi-box span {
            font-size: 11px;
            color: #64748b;
        }

        .field-label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 14px;
            color: #0f172a;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .field-input:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .15);
            outline: none;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .field-input {
            padding-right: 92px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 10px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
            background: #f1f5f9;
            color: #0f766e;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .password-toggle:hover {
            background: #ccfbf1;
            color: #115e59;
        }

        .password-strength {
            margin-top: 8px;
        }

        .password-strength-track {
            width: 100%;
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            transition: width .2s ease, background-color .2s ease;
            background: #ef4444;
        }

        .password-strength-text {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
        }

        .password-rules {
            margin-top: 8px;
            padding-left: 0;
            list-style: none;
            display: grid;
            gap: 4px;
        }

        .password-rule {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .15s ease;
        }

        .password-rule::before {
            content: '•';
            color: #94a3b8;
            font-weight: 900;
        }

        .password-rule.ok {
            color: #0f766e;
            font-weight: 700;
        }

        .password-rule.ok::before {
            content: '✓';
            color: #10b981;
        }

        .cta-btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 14px;
            font-weight: 800;
            color: #f8fafc;
            background: linear-gradient(135deg, #0f766e, #0e7490);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .cta-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(15, 118, 110, .28);
        }

        .auth-link {
            color: #0f766e;
            font-weight: 700;
        }

        .auth-link:hover {
            color: #115e59;
        }

        .auth-alert {
            margin-bottom: 16px;
            border-radius: 12px;
            border: 1px solid #bae6fd;
            background: #ecfeff;
            color: #155e75;
            padding: 10px 12px;
            font-size: 13px;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes panelInLeft {
            from {
                opacity: 0;
                transform: translateX(-16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes panelInRight {
            from {
                opacity: 0;
                transform: translateX(16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes contentIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991px) {
            .auth-card {
                grid-template-columns: 1fr;
            }

            .auth-brand {
                border-right: 0;
                border-bottom: 1px solid var(--ms-border);
                padding: 28px;
            }

            .auth-panel {
                padding: 26px;
            }
        }
    </style>
</head>

<body>
    <div class="publishing-grid" aria-hidden="true"></div>

    <main class="auth-shell">
        <section class="auth-card">
            <aside class="auth-brand">
                <span class="cover-chip">MS Publishing Portal</span>

                <h1 class="hero-title text-4xl md:text-5xl font-bold leading-tight text-slate-900">
                    Ruang Kerja Penerbitan yang Modern.
                </h1>

                <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-xl">
                    Kelola naskah, kolaborasi produksi, pembayaran, dan layanan penulis dalam satu ekosistem MS
                    Publishing.
                </p>

                <div class="brand-kpi">
                    <div class="kpi-box">
                        <strong>A4/A5</strong>
                        <span>Smart page counter</span>
                    </div>
                    <div class="kpi-box">
                        <strong>24/7</strong>
                        <span>Tracking progres</span>
                    </div>
                    <div class="kpi-box">
                        <strong>1 Portal</strong>
                        <span>Author & backoffice</span>
                    </div>
                </div>
            </aside>

            <section class="auth-panel">
                <div class="panel-stagger">
                    {{ $slot }}
                </div>
            </section>
        </section>
    </main>

    <script>
        (function() {
            const toggles = Array.from(document.querySelectorAll('[data-toggle-password]'));
            const strengthInputs = Array.from(document.querySelectorAll('[data-password-strength]'));

            toggles.forEach((toggle) => {
                const targetId = toggle.getAttribute('data-target');
                if (!targetId) {
                    return;
                }

                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }

                toggle.addEventListener('click', () => {
                    const isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    toggle.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
                    toggle.setAttribute('aria-label', isPassword ? 'Sembunyikan password' :
                        'Tampilkan password');
                });
            });

            const evaluateStrength = (value) => {
                const password = value || '';
                if (password.length === 0) {
                    return {
                        score: 0,
                        label: 'Belum diisi',
                        color: '#cbd5e1'
                    };
                }

                let score = 0;

                if (password.length >= 8) score += 1;
                if (password.length >= 12) score += 1;
                if (/[A-Z]/.test(password)) score += 1;
                if (/[a-z]/.test(password)) score += 1;
                if (/\d/.test(password)) score += 1;
                if (/[^A-Za-z0-9]/.test(password)) score += 1;

                if (score <= 2) {
                    return {
                        score: 1,
                        label: 'Weak',
                        color: '#ef4444'
                    };
                }

                if (score <= 4) {
                    return {
                        score: 2,
                        label: 'Medium',
                        color: '#f59e0b'
                    };
                }

                return {
                    score: 3,
                    label: 'Strong',
                    color: '#10b981'
                };
            };

            strengthInputs.forEach((input) => {
                const targetId = input.getAttribute('data-strength-target');
                const rulesTargetId = input.getAttribute('data-password-rules-target');
                if (!targetId) {
                    return;
                }

                const meter = document.getElementById(targetId);
                if (!meter) {
                    return;
                }

                const fill = meter.querySelector('[data-strength-fill]');
                const text = meter.querySelector('[data-strength-text]');
                if (!fill || !text) {
                    return;
                }

                const rulesContainer = rulesTargetId ? document.getElementById(rulesTargetId) : null;

                const syncRules = (passwordValue) => {
                    if (!rulesContainer) {
                        return;
                    }

                    const checks = {
                        min8: (passwordValue || '').length >= 8,
                        upper: /[A-Z]/.test(passwordValue || ''),
                        lower: /[a-z]/.test(passwordValue || ''),
                        digit: /\d/.test(passwordValue || ''),
                        symbol: /[^A-Za-z0-9]/.test(passwordValue || ''),
                    };

                    Array.from(rulesContainer.querySelectorAll('[data-rule]')).forEach((item) => {
                        const ruleName = item.getAttribute('data-rule') || '';
                        item.classList.toggle('ok', Boolean(checks[ruleName]));
                    });
                };

                const render = () => {
                    const state = evaluateStrength(input.value);
                    const widthMap = {
                        0: '0%',
                        1: '33%',
                        2: '66%',
                        3: '100%'
                    };

                    fill.style.width = widthMap[state.score] || '0%';
                    fill.style.backgroundColor = state.color;
                    text.textContent = 'Kekuatan password: ' + state.label;
                    text.style.color = state.color;
                    syncRules(input.value || '');
                };

                input.addEventListener('input', render);
                render();
            });
        })();
    </script>
</body>

</html>
