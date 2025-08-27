<?php

namespace App\Services;

use App\Models\GoldQuote;
use App\Services\GoldPrice\GoldPriceDriverInterface;
use App\Services\GoldPrice\CustomDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class GoldPriceService
{
    protected GoldPriceDriverInterface $driver;

    public function __construct()
    {
        $provider = Config::get('gold.provider', 'custom');
        $this->driver = $this->resolveDriver($provider);
    }

    protected function resolveDriver(string $provider): GoldPriceDriverInterface
    {
        return match ($provider) {
            'custom' => new CustomDriver(),
            default => new CustomDriver(),
        };
    }

    public function fetchPrice(): array
    {
        $quote = $this->driver->fetchPrice();

        // Normalize units
        $unit = Config::get('gold.unit', 'g');
        if ($unit === 'oz') {
            $quote['bid'] = $this->convertOzToGrams($quote['bid']);
            $quote['ask'] = $this->convertOzToGrams($quote['ask']);
        }

        // Normalize currency
        $currency = Config::get('gold.currency', 'EUR');
        if ($currency !== 'EUR') {
            // Assuming EUR is base, convert to EUR
            $quote['bid'] = $this->convertToEUR($quote['bid'], $currency);
            $quote['ask'] = $this->convertToEUR($quote['ask'], $currency);
        }

        // Save to database
        GoldQuote::create([
            'provider' => Config::get('gold.provider'),
            'bid' => $quote['bid'],
            'ask' => $quote['ask'],
            'unit' => 'g',
            'currency' => 'EUR',
            'fetched_at' => now(),
        ]);

        // Cache the latest price
        Cache::put('gold:latest', $quote, now()->addSeconds(60));

        return $quote;
    }

    public function getLatestPrice(): ?array
    {
        return Cache::get('gold:latest');
    }

    protected function convertOzToGrams(float $pricePerOz): float
    {
        return $pricePerOz / 31.1035; // 1 oz = 31.1035 grams
    }

    protected function convertToEUR(float $price, string $fromCurrency): float
    {
        // Simple conversion rates - in production, use a proper exchange rate API
        $rates = [
            'USD' => 0.85,
            'GBP' => 1.15,
        ];

        return $price * ($rates[$fromCurrency] ?? 1);
    }
}
