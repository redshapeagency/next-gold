<?php

namespace App\Services\GoldPrice\Drivers;

use App\Services\GoldPrice\Contracts\GoldPriceDriverInterface;
use Illuminate\Support\Facades\Http;

class CustomDriver implements GoldPriceDriverInterface
{
    public function fetch(): ?array
    {
        $apiUrl = config('gold.api_url');
        $apiKey = config('gold.api_key');

        if (!$apiUrl) {
            throw new \Exception('API URL non configurato');
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => $apiKey ? "Bearer {$apiKey}" : null,
                'Accept' => 'application/json',
            ])
            ->get($apiUrl);

        if (!$response->successful()) {
            throw new \Exception("API request failed: {$response->status()}");
        }

        $data = $response->json();

        // Adatta la risposta al formato standard
        return [
            'provider' => 'custom',
            'bid' => $data['bid'] ?? $data['buy'] ?? null,
            'ask' => $data['ask'] ?? $data['sell'] ?? null,
            'unit' => $data['unit'] ?? config('gold.unit'),
            'currency' => $data['currency'] ?? config('gold.currency'),
        ];
    }
}
