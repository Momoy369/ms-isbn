<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice POS {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 11px;
        }

        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Invoice POS</div>
        <div class="muted">No: {{ $invoice->invoice_number }}</div>
        <div class="muted">Tanggal: {{ optional($invoice->created_at)->format('d M Y H:i') }}</div>
    </div>

    <table>
        <tr>
            <th style="width: 30%;">Data Customer</th>
            <td>
                <div><strong>{{ $invoice->order->customer_name }}</strong></div>
                <div>{{ $invoice->order->customer_phone ?: '-' }}</div>
                <div>{{ $invoice->order->customer_email ?: '-' }}</div>
                <div class="muted">Order: {{ $invoice->order->order_number }}</div>
            </td>
        </tr>
        <tr>
            <th>Termin</th>
            <td>Termin {{ $invoice->installment_number }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="badge">{{ strtoupper($invoice->status) }}</span></td>
        </tr>
        <tr>
            <th>Jatuh Tempo</th>
            <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
        </tr>
        <tr>
            <th>Deskripsi</th>
            <td>{{ $invoice->description }}</td>
        </tr>
        <tr>
            <th>No Ref Jasa</th>
            <td>{{ $invoice->order->service_order_ref ?: '-' }}</td>
        </tr>
        <tr>
            <th>Ref Marketing</th>
            <td>{{ $invoice->order->marketing_ref ?: '-' }}</td>
        </tr>
        @if (!empty($invoice->order->publishing_metadata))
            <tr>
                <th>Ringkasan Overage</th>
                <td>
                    @php($meta = $invoice->order->publishing_metadata)
                    <div>A4: {{ (int) ($meta['a4_pages'] ?? 0) }} halaman (limit {{ (int) ($meta['a4_limit'] ?? 0) }},
                        lebih {{ (int) ($meta['a4_over_pages'] ?? 0) }})</div>
                    <div>{{ strtoupper((string) ($meta['selected_print_paper'] ?? 'A5')) }}:
                        {{ (int) ($meta['selected_print_pages'] ?? 0) }} halaman (limit
                        {{ (int) ($meta['print_limit'] ?? 0) }}, lebih {{ (int) ($meta['print_over_pages'] ?? 0) }})
                    </div>
                    <div>Layout: Rp {{ number_format((float) ($meta['layout_fee'] ?? 0), 0, ',', '.') }}, Editing: Rp
                        {{ number_format((float) ($meta['editing_fee'] ?? 0), 0, ',', '.') }}, Cetak: Rp
                        {{ number_format((float) ($meta['print_fee'] ?? 0), 0, ',', '.') }}</div>
                </td>
            </tr>
        @endif
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Jenis</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $item->item_type)) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="text-right"><strong>Subtotal Order</strong></td>
                <td class="text-right">Rp {{ number_format($invoice->order->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Diskon</strong></td>
                <td class="text-right">Rp {{ number_format($invoice->order->discount_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Total Order</strong></td>
                <td class="text-right">Rp {{ number_format($invoice->order->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Nominal Invoice Ini</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>Catatan: {{ $invoice->notes ?: '-' }}</div>
        @if ($invoice->paid_at)
            <div>Lunas pada: {{ $invoice->paid_at->format('d M Y H:i') }}</div>
        @endif
    </div>
</body>

</html>
