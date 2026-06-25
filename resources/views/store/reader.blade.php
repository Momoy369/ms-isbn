<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Ebook {{ $order->item->title ?? '' }}</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
        }

        header {
            background: #111827;
            border-bottom: 1px solid #1f2937;
            padding: .7rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .8rem;
            flex-wrap: wrap;
        }

        header img {
            max-height: 34px;
        }

        a {
            color: #67e8f9;
            text-decoration: none;
            font-weight: 700;
        }

        iframe {
            width: 100%;
            height: calc(100vh - 68px);
            border: 0;
            background: #fff;
        }
    </style>
</head>

<body>
    <header>
        <div>
            <img src="{{ asset('logowide2.png') }}" alt="MS ISBN">
            <div style="font-size:.86rem; color:#94a3b8;">{{ $order->item->title ?? 'Ebook Reader' }} |
                {{ $order->order_number }}</div>
        </div>
        <a href="{{ route('store.track.show', $order->order_number) }}">Kembali ke status order</a>
    </header>

    <iframe src="{{ $ebookUrl }}" title="Ebook Reader"></iframe>
</body>

</html>
