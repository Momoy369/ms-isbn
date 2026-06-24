<?php

namespace App\Services;

use App\Models\AuthorInvoice;
use Illuminate\Support\Facades\Http;

class IpaymuService
{
    public function createInvoiceCheckout(AuthorInvoice $invoice): array
    {
        $apiKey = (string) config('services.ipaymu.api_key');
        $va = (string) config('services.ipaymu.va');
        $endpoint = rtrim((string) config('services.ipaymu.base_url', 'https://my.ipaymu.com/api/v2'), '/');

        if (empty($apiKey) || empty($va)) {
            $ref = 'IPAYMU-DUMMY-' . now()->format('YmdHis') . '-' . $invoice->id;

            return [
                'reference' => $ref,
                'checkout_url' => url('/author/invoices/' . $invoice->id),
                'expires_at' => now()->addHours(12),
                'raw' => null,
                'is_dummy' => true,
            ];
        }

        $payload = [
            'product' => [$invoice->description],
            'qty' => [1],
            'price' => [(float) $invoice->amount],
            'description' => $invoice->invoice_number,
            'returnUrl' => url('/author/invoices/' . $invoice->id),
            'cancelUrl' => url('/author/invoices/' . $invoice->id),
            'notifyUrl' => url('/payments/ipaymu/callback'),
            'referenceId' => $invoice->invoice_number,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'va' => $va,
            'apikey' => $apiKey,
        ])->post($endpoint . '/payment', $payload);

        $json = $response->json();

        if (!$response->ok() || (int) data_get($json, 'Status') !== 200) {
            return [
                'reference' => null,
                'checkout_url' => null,
                'expires_at' => null,
                'raw' => $json,
                'is_dummy' => false,
            ];
        }

        return [
            'reference' => (string) data_get($json, 'Data.SessionID'),
            'checkout_url' => (string) data_get($json, 'Data.Url'),
            'expires_at' => now()->addHours(12),
            'raw' => $json,
            'is_dummy' => false,
        ];
    }
}
