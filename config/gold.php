<?php

return [
    'providers' => [
        'custom' => \App\Services\GoldPrice\Drivers\CustomDriver::class,
        'metals_api' => \App\Services\GoldPrice\Drivers\MetalsApiDriver::class,
    ],

    'default_provider' => env('GOLD_PROVIDER', 'custom'),

    'api_url' => env('GOLD_API_URL'),
    'api_key' => env('GOLD_API_KEY'),
    'unit' => env('GOLD_UNIT', 'oz'),
    'currency' => env('GOLD_CURRENCY', 'EUR'),
    'fetch_interval' => env('GOLD_FETCH_INTERVAL', 60),

    'cache_key' => 'gold:latest',
    'cache_duration' => 60, // seconds

    'conversion_rates' => [
        'oz_to_g' => 31.1034768,
    ],
];
