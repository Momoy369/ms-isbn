<?php

namespace App\Http\Controllers;

use App\Models\PosInvoice;
use Dompdf\Dompdf;

class PosInvoiceDocumentController extends Controller
{
    public function download(PosInvoice $invoice)
    {
        $invoice->load(['order.items', 'order.linkedUser', 'verifier']);

        $html = view('invoices.pos-pdf', compact('invoice'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice_pos_' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}
