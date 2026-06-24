<?php

namespace App\Http\Controllers;

use App\Models\AuthorBookOrder;
use App\Models\AuthorInvoice;
use App\Models\AuthorServiceOrder;
use App\Services\IpaymuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function checkout(AuthorInvoice $invoice, IpaymuService $ipaymu)
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        if ($invoice->status !== 'pending') {
            return back()->with('warning', 'Invoice tidak dalam status pending.');
        }

        $checkout = $ipaymu->createInvoiceCheckout($invoice);

        if (empty($checkout['checkout_url'])) {
            $raw = $checkout['raw'] ?? null;
            $msg = 'Gagal membuat checkout iPaymu.';

            if (is_array($raw)) {
                $gatewayMessage = data_get($raw, 'response.Message') ?: data_get($raw, 'response.message') ?: data_get($raw, 'error');
                if (!empty($gatewayMessage)) {
                    $msg .= ' Detail: ' . $gatewayMessage;
                }
            }

            return back()->with('danger', $msg);
        }

        $invoice->update([
            'payment_method' => 'ipaymu',
            'payment_gateway' => 'ipaymu',
            'gateway_reference' => $checkout['reference'] ?? null,
            'gateway_checkout_url' => $checkout['checkout_url'],
            'gateway_expires_at' => $checkout['expires_at'] ?? null,
            'payment_reference' => $checkout['reference'] ?? $invoice->payment_reference,
        ]);

        return redirect()->away($checkout['checkout_url']);
    }

    public function callback(Request $request)
    {
        if (!$this->isCallbackSignatureValid($request)) {
            return response()->json(['message' => 'invalid signature'], 401);
        }

        $reference = (string) ($request->input('reference_id') ?: $request->input('referenceId') ?: $request->input('reference'));
        $status = strtolower((string) ($request->input('status') ?: $request->input('Status')));

        if (empty($reference)) {
            return response()->json(['message' => 'reference missing'], 422);
        }

        $invoice = AuthorInvoice::where('invoice_number', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'invoice not found'], 404);
        }

        if (in_array($status, ['success', 'berhasil', 'paid', 'settlement'], true)) {
            if ($invoice->status === 'paid') {
                return response()->json(['message' => 'ok']);
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'ipaymu',
                'payment_gateway' => 'ipaymu',
                'payment_reference' => $reference,
                'gateway_reference' => $invoice->gateway_reference ?: $reference,
            ]);

            AuthorBookOrder::where('author_invoice_id', $invoice->id)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            AuthorServiceOrder::where('author_invoice_id', $invoice->id)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($invoice->user_id) {
                app(NotificationService::class)->send(
                    $invoice->user_id,
                    'Pembayaran Berhasil',
                    'Pembayaran invoice ' . $invoice->invoice_number . ' berhasil. Akses file final terbuka setelah seluruh berkas final percetakan lengkap.',
                    $invoice->book_id
                );
            }

            Log::info('iPaymu callback marked invoice as paid.', [
                'invoice_id' => $invoice->id,
                'reference' => $reference,
                'status' => $status,
            ]);
        } elseif (in_array($status, ['failed', 'expired', 'cancelled', 'canceled', 'deny'], true)) {
            $invoice->update([
                'status' => 'cancelled',
                'payment_method' => $invoice->payment_method ?: 'ipaymu',
                'payment_gateway' => 'ipaymu',
                'payment_reference' => $reference,
                'gateway_reference' => $invoice->gateway_reference ?: $reference,
            ]);

            AuthorBookOrder::where('author_invoice_id', $invoice->id)->update([
                'status' => 'cancelled',
            ]);

            AuthorServiceOrder::where('author_invoice_id', $invoice->id)->update([
                'status' => 'cancelled',
            ]);

            Log::info('iPaymu callback marked invoice as cancelled.', [
                'invoice_id' => $invoice->id,
                'reference' => $reference,
                'status' => $status,
            ]);
        }

        return response()->json(['message' => 'ok']);
    }

    private function isCallbackSignatureValid(Request $request): bool
    {
        $mustVerify = (bool) config('services.ipaymu.verify_callback_signature', true);

        if (!$mustVerify) {
            return true;
        }

        $secret = (string) config('services.ipaymu.callback_secret', '');

        if ($secret === '') {
            Log::warning('iPaymu callback signature verification skipped because secret is empty.');

            return true;
        }

        $provided = (string) ($request->header('X-Signature')
            ?: $request->header('signature')
            ?: $request->input('signature')
            ?: '');

        if ($provided === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $provided);
    }
}
