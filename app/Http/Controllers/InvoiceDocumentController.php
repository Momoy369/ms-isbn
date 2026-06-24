<?php

namespace App\Http\Controllers;

use App\Models\AuthorInvoice;
use Dompdf\Dompdf;

class InvoiceDocumentController extends Controller
{
    public function download(AuthorInvoice $invoice)
    {
        if (auth()->user()->role === 'author' && $invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoice->load(['book', 'user']);

        $html = view('invoices.pdf', compact('invoice'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice_' . $invoice->invoice_number . '.pdf"',
        ]);
    }
}
