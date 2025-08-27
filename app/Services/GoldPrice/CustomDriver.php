<?php

namespace App\Services\GoldPrice;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class CustomDriver implements GoldPriceDriverInterface
{
    public function fetchPrice(): array
    {
        $apiUrl = Config::get('gold.api_url');
        $apiKey = Config::get('gold.api_key');

        if (!$apiUrl || !$apiKey) {
            // Return mock data if no API configured
            return [
                'bid' => 50.0 + rand(-500, 500) / 100,
                'ask' => 51.0 + rand(-500, 500) / 100,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($apiUrl);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch gold price from API');
        }

        $data = $response->json();

        // Assuming the API returns bid and ask in the response
        return [
            'bid' => $data['bid'] ?? 50.0,
            'ask' => $data['ask'] ?? 51.0,
        ];
    }
}
