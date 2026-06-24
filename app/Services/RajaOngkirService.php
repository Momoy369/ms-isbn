<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
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

        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->asForm()->post('https://api.rajaongkir.com/starter/cost', [
                    'origin' => $originCityId,
                    'destination' => $destinationCityId,
                    'weight' => max(100, $weightGram),
                    'courier' => strtolower($courier),
                ]);

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
}
