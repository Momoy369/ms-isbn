<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use App\Models\ExternalSalesRecord;
use Illuminate\Http\Request;

class AdminFinanceReportController extends Controller
{
    public function exportInvoicesCsv(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = AuthorInvoice::with(['book', 'user'])->orderBy('created_at');

        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        $rows = $query->get();

        $filename = 'finance_invoices_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Tanggal', 'Author', 'Buku', 'Jenis', 'Status', 'Amount', 'Metode']);

            foreach ($rows as $row) {
                $typeLabel = match ($row->invoice_type) {
                    'package_dp' => 'Paket (DP 50%)',
                    'package_final' => 'Paket (Pelunasan 50%)',
                    'dummy_print' => 'Cetak Dummy',
                    'service_addon' => 'Layanan Tambahan',
                    default => 'Lainnya',
                };

                fputcsv($handle, [
                    $row->invoice_number,
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                    optional($row->user)->name,
                    optional($row->book)->judul,
                    $typeLabel,
                    $row->status,
                    (float) $row->amount,
                    $row->payment_method,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportSalesCsv(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = ExternalSalesRecord::with(['book', 'inputBy'])->orderBy('sold_at');

        if ($start) {
            $query->whereDate('sold_at', '>=', $start);
        }

        if ($end) {
            $query->whereDate('sold_at', '<=', $end);
        }

        $rows = $query->get();
        $filename = 'external_sales_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Buku', 'Channel', 'Format', 'Qty', 'Harga', 'Gross', 'Royalti20']);

            foreach ($rows as $row) {
                $unitPriceForRoyalty = (float) ((optional($row->book)->selling_price ?? 0) > 0
                    ? $row->book->selling_price
                    : $row->unit_price);

                fputcsv($handle, [
                    optional($row->sold_at)->format('Y-m-d'),
                    optional($row->book)->judul,
                    $row->channel,
                    $row->format,
                    (int) $row->quantity,
                    (float) $row->unit_price,
                    (float) $row->gross_amount,
                    ((int) $row->quantity) * $unitPriceForRoyalty * 0.20,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
