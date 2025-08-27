<?php

namespace App\Services\GoldPrice\Drivers;

use App\Services\GoldPrice\Contracts\GoldPriceDriverInterface;
use Illuminate\Support\Facades\Http;

class MetalsApiDriver implements GoldPriceDriverInterface
{
    public function fetch(): ?array
    {
        $apiKey = config('gold.api_key');

        if (!$apiKey) {
            throw new \Exception('API Key non configurato per Metals API');
        }

        $response = Http::timeout(30)
            ->get('https://metals-api.com/api/latest', [
                'access_key' => $apiKey,
                'base' => 'EUR',
                'symbols' => 'XAU',
            ]);

        if (!$response->successful()) {
            throw new \Exception("Metals API request failed: {$response->status()}");
        }

        $data = $response->json();

        if (!$data['success']) {
            throw new \Exception('Metals API error: ' . ($data['error']['info'] ?? 'Unknown error'));
        }

        $goldRate = $data['rates']['XAU'] ?? null;
        
        if (!$goldRate) {
            throw new \Exception('Gold rate not found in API response');
        }

        // Metals API restituisce EUR per oncia troy, convertiamo in EUR per grammo
        $eurPerGram = $goldRate / config('gold.conversion_rates.oz_to_g');

        return [
            'provider' => 'metals_api',
            'bid' => $eurPerGram * 0.98, // Spread del 2%
            'ask' => $eurPerGram * 1.02,
            'unit' => 'g',
            'currency' => 'EUR',
        ];
    }
}
