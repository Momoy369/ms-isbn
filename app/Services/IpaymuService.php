<?php

namespace App\Services;

use App\Models\AuthorInvoice;
use App\Models\StoreOrder;
use Illuminate\Support\Facades\Http;
use Throwable;

class IpaymuService
{
    public function createInvoiceCheckout(AuthorInvoice $invoice): array
    {
        $apiKey = (string) config('services.ipaymu.api_key');
        $va = (string) config('services.ipaymu.va');
        $endpoint = rtrim((string) config('services.ipaymu.base_url', 'https://my.ipaymu.com/api/v2'), '/');
        $verifySsl = (bool) config('services.ipaymu.verify_ssl', true);

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
            'description' => [$invoice->invoice_number],
            'returnUrl' => url('/author/invoices/' . $invoice->id),
            'cancelUrl' => url('/author/invoices/' . $invoice->id),
            'notifyUrl' => url('/payments/ipaymu/callback'),
            'referenceId' => $invoice->invoice_number,
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $requestBody = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $va . ':' . $requestBody . ':' . $apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp = now()->format('YmdHis');

        try {
            $response = Http::timeout(25)
                ->withOptions([
                    'verify' => $verifySsl,
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'va' => $va,
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                ])->send('POST', $endpoint . '/payment', [
                        'body' => $jsonBody,
                    ]);

            $json = $response->json();
        } catch (Throwable $e) {
            return [
                'reference' => null,
                'checkout_url' => null,
                'expires_at' => null,
                'raw' => [
                    'error' => $e->getMessage(),
                ],
                'is_dummy' => false,
            ];
        }

        if (!$response->ok() || (int) data_get($json, 'Status') !== 200) {
            return [
                'reference' => null,
                'checkout_url' => null,
                'expires_at' => null,
                'raw' => [
                    'http_status' => $response->status(),
                    'response' => $json,
                ],
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

    public function createStoreOrderCheckout(StoreOrder $order): array
    {
        $apiKey = (string) config('services.ipaymu.api_key');
        $va = (string) config('services.ipaymu.va');
        $endpoint = rtrim((string) config('services.ipaymu.base_url', 'https://my.ipaymu.com/api/v2'), '/');
        $verifySsl = (bool) config('services.ipaymu.verify_ssl', true);

        if (empty($apiKey) || empty($va)) {
            $ref = 'IPAYMU-STORE-' . now()->format('YmdHis') . '-' . $order->id;

            return [
                'reference' => $ref,
                'checkout_url' => route('store.track.show', ['orderNumber' => $order->order_number]),
                'expires_at' => now()->addHours(12),
                'raw' => null,
                'is_dummy' => true,
            ];
        }

        $qty = 1;
        $price = (float) $order->subtotal;
        $productName = (string) optional($order->item)->title ?: ('Order ' . $order->order_number);

        $payload = [
            'product' => [$productName],
            'qty' => [$qty],
            'price' => [$price],
            'description' => [$order->order_number],
            'buyerName' => $order->customer_name,
            'buyerPhone' => $order->customer_phone,
            'buyerEmail' => $order->customer_email,
            'returnUrl' => route('store.track.show', ['orderNumber' => $order->order_number]),
            'cancelUrl' => route('store.track.show', ['orderNumber' => $order->order_number]),
            'notifyUrl' => route('payments.ipaymu.callback'),
            'referenceId' => $order->order_number,
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $requestBody = strtolower(hash('sha256', $jsonBody));
        $stringToSign = 'POST:' . $va . ':' . $requestBody . ':' . $apiKey;
        $signature = hash_hmac('sha256', $stringToSign, $apiKey);
        $timestamp = now()->format('YmdHis');

        try {
            $response = Http::timeout(25)
                ->withOptions([
                    'verify' => $verifySsl,
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'va' => $va,
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                ])->send('POST', $endpoint . '/payment', [
                        'body' => $jsonBody,
                    ]);

            $json = $response->json();
        } catch (Throwable $e) {
            return [
                'reference' => null,
                'checkout_url' => null,
                'expires_at' => null,
                'raw' => [
                    'error' => $e->getMessage(),
                ],
                'is_dummy' => false,
            ];
        }

        if (!$response->ok() || (int) data_get($json, 'Status') !== 200) {
            return [
                'reference' => null,
                'checkout_url' => null,
                'expires_at' => null,
                'raw' => [
                    'http_status' => $response->status(),
                    'response' => $json,
                ],
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
