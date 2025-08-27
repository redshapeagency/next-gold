<?php

namespace App\Services\GoldPrice;

use App\Models\GoldQuote;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoldPriceService
{
    protected $driver;

    public function __construct()
    {
        $this->driver = $this->createDriver();
    }

    protected function createDriver()
    {
        $provider = config('gold.default_provider');
        $driverClass = config("gold.providers.{$provider}");
        
        if (!$driverClass || !class_exists($driverClass)) {
            throw new \InvalidArgumentException("Gold price driver [{$provider}] not found.");
        }

        return new $driverClass();
    }

    public function fetchLatestPrice(): ?GoldQuote
    {
        try {
            $data = $this->driver->fetch();
            
            if (!$data) {
                return null;
            }

            // Normalizza i dati
            $normalized = $this->normalizeData($data);
            
            // Salva nel database
            $quote = GoldQuote::create([
                'provider' => $normalized['provider'],
                'bid' => $normalized['bid'],
                'ask' => $normalized['ask'],
                'unit' => $normalized['unit'],
                'currency' => $normalized['currency'],
                'fetched_at' => now(),
            ]);

            // Cache per 60 secondi
            Cache::put(config('gold.cache_key'), $quote, config('gold.cache_duration'));

            Log::info('Gold price fetched successfully', [
                'provider' => $quote->provider,
                'bid' => $quote->bid,
                'ask' => $quote->ask,
            ]);

            return $quote;
        } catch (\Exception $e) {
            Log::error('Failed to fetch gold price', [
                'error' => $e->getMessage(),
                'provider' => config('gold.default_provider'),
            ]);
            
            return null;
        }
    }

    public function getLatestPrice(): ?GoldQuote
    {
        // Prova prima dalla cache
        $cached = Cache::get(config('gold.cache_key'));
        if ($cached instanceof GoldQuote) {
            return $cached;
        }

        // Altrimenti prendi l'ultimo dal database
        return GoldQuote::latest('fetched_at')->first();
    }

    protected function normalizeData(array $data): array
    {
        $normalized = [
            'provider' => $data['provider'] ?? config('gold.default_provider'),
            'bid' => $data['bid'],
            'ask' => $data['ask'],
            'unit' => $data['unit'] ?? config('gold.unit'),
            'currency' => $data['currency'] ?? config('gold.currency'),
        ];

        // Converti da once a grammi se necessario
        if ($normalized['unit'] === 'oz' && config('gold.unit') === 'g') {
            $ozToG = config('gold.conversion_rates.oz_to_g');
            $normalized['bid'] = $normalized['bid'] / $ozToG;
            $normalized['ask'] = $normalized['ask'] / $ozToG;
            $normalized['unit'] = 'g';
        }

        return $normalized;
    }

    public function testConnection(): array
    {
        try {
            $data = $this->driver->fetch();
            
            return [
                'success' => !empty($data),
                'data' => $data,
                'message' => $data ? 'Connessione riuscita' : 'Nessun dato ricevuto',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Errore: ' . $e->getMessage(),
            ];
        }
    }
}
