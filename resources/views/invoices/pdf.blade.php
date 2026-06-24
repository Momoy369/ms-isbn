<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <img src="{{ public_path('ms-logo.png') }}" alt="logo" style="height: 52px;">
            <div class="title">INVOICE</div>
        </div>
        <div>
            <strong>{{ $invoice->invoice_number }}</strong><br>
            {{ optional($invoice->created_at)->format('d M Y') }}
        </div>
    </div>

    <div class="box">
        <strong>Author:</strong> {{ optional($invoice->user)->name }}<br>
        <strong>Buku:</strong> {{ optional($invoice->book)->judul }}<br>
        <strong>Status:</strong> {{ strtoupper($invoice->status) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Jenis</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description }}</td>
                <td>{{ $invoice->getTypeLabel() }}</td>
                <td>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:16px;">Terima kasih.</p>
</body>

</html>
