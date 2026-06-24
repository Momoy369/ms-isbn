<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RajaOngkirService
{
    public function provincesWithMeta(): array
    {
        $apiKey = (string) config('services.rajaongkir.key');

        if (empty($apiKey)) {
            return [
                'data' => $this->dummyProvinces(),
                'is_fallback' => true,
                'message' => 'API key RajaOngkir belum disetel. Sistem menggunakan data dummy.',
            ];
        }

        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'verify' => config('services.rajaongkir.verify_ssl', true),
                ])
                ->withHeaders(['key' => $apiKey])
                ->get('https://api.rajaongkir.com/starter/province');

            if (!$response->ok()) {
                return [
                    'data' => $this->dummyProvinces(),
                    'is_fallback' => true,
                    'message' => 'RajaOngkir merespons gagal (' . $response->status() . '). Data dummy dipakai sementara.',
                ];
            }

            return [
                'data' => data_get($response->json(), 'rajaongkir.results', []),
                'is_fallback' => false,
                'message' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('RajaOngkir provinces fallback used.', [
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => $this->dummyProvinces(),
                'is_fallback' => true,
                'message' => 'Koneksi API RajaOngkir gagal (' . $e->getMessage() . '). Data dummy dipakai.',
            ];
        }
    }

    public function provinces(): array
    {
        return $this->provincesWithMeta()['data'];
    }

    public function citiesWithMeta(string $provinceId): array
    {
        $apiKey = (string) config('services.rajaongkir.key');

        if (empty($apiKey)) {
            return [
                'data' => $this->dummyCities(),
                'is_fallback' => true,
                'message' => 'API key RajaOngkir belum disetel. Sistem menggunakan data kota dummy.',
            ];
        }

        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'verify' => config('services.rajaongkir.verify_ssl', true),
                ])
                ->withHeaders(['key' => $apiKey])
                ->get('https://api.rajaongkir.com/starter/city', [
                    'province' => $provinceId,
                ]);

            if (!$response->ok()) {
                return [
                    'data' => $this->dummyCities(),
                    'is_fallback' => true,
                    'message' => 'RajaOngkir merespons gagal (' . $response->status() . '). Data kota dummy dipakai.',
                ];
            }

            return [
                'data' => data_get($response->json(), 'rajaongkir.results', []),
                'is_fallback' => false,
                'message' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('RajaOngkir cities fallback used.', [
                'province_id' => $provinceId,
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => $this->dummyCities(),
                'is_fallback' => true,
                'message' => 'Koneksi API kota RajaOngkir gagal (' . $e->getMessage() . '). Data dummy dipakai.',
            ];
        }
    }

    public function cities(string $provinceId): array
    {
        return $this->citiesWithMeta($provinceId)['data'];
    }

    public function estimateCost(string $destinationCityId, int $weightGram, string $courier = 'jne'): array
    {
        $apiKey = (string) config('services.rajaongkir.key');
        $originCityId = (string) config('services.rajaongkir.origin_city_id', '501');

        if (empty($apiKey)) {
            $dummyCost = 12000 + (int) ceil($weightGram / 1000) * 3500;

            return [
                'service' => 'DUMMY-REG',
                'description' => 'Estimasi Dummy (API key RajaOngkir belum diset)',
                'cost' => $dummyCost,
                'etd' => '2-5',
                'raw' => null,
            ];
        }

        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'verify' => config('services.rajaongkir.verify_ssl', true),
                ])
                ->withHeaders([
                    'key' => $apiKey,
                ])->asForm()->post('https://api.rajaongkir.com/starter/cost', [
                        'origin' => $originCityId,
                        'destination' => $destinationCityId,
                        'weight' => max(100, $weightGram),
                        'courier' => strtolower($courier),
                    ]);
        } catch (Throwable $e) {
            Log::warning('RajaOngkir cost fallback used.', [
                'destination_city_id' => $destinationCityId,
                'error' => $e->getMessage(),
            ]);

            $dummyCost = 12000 + (int) ceil($weightGram / 1000) * 3500;

            return [
                'service' => 'DUMMY-REG',
                'description' => 'Fallback dummy (koneksi RajaOngkir gagal)',
                'cost' => $dummyCost,
                'etd' => '2-5',
                'raw' => null,
            ];
        }

        $json = $response->json();

        $costs = data_get($json, 'rajaongkir.results.0.costs', []);
        $first = $costs[0] ?? null;
        $firstCost = data_get($first, 'cost.0.value');

        if (!$response->ok() || !$first || !$firstCost) {
            $dummyCost = 12000 + (int) ceil($weightGram / 1000) * 3500;

            return [
                'service' => 'DUMMY-REG',
                'description' => 'Fallback dummy (response RajaOngkir tidak valid)',
                'cost' => $dummyCost,
                'etd' => '2-5',
                'raw' => $json,
            ];
        }

        return [
            'service' => data_get($first, 'service', 'REG'),
            'description' => data_get($first, 'description', 'Regular Service'),
            'cost' => (float) $firstCost,
            'etd' => data_get($first, 'cost.0.etd', '-'),
            'raw' => $json,
        ];
    }

    private function dummyProvinces(): array
    {
        return [
            ['province_id' => '1', 'province' => 'DKI Jakarta'],
            ['province_id' => '9', 'province' => 'Jawa Barat'],
            ['province_id' => '11', 'province' => 'Jawa Timur'],
        ];
    }

    private function dummyCities(): array
    {
        return [
            ['city_id' => '39', 'city_name' => 'Bandung', 'province' => 'Jawa Barat'],
            ['city_id' => '152', 'city_name' => 'Jakarta Selatan', 'province' => 'DKI Jakarta'],
            ['city_id' => '444', 'city_name' => 'Surabaya', 'province' => 'Jawa Timur'],
        ];
    }
}
