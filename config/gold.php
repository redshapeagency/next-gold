<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gold Price Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for gold price fetching service
    |
    */

    'provider' => env('GOLD_PROVIDER', 'custom'),

    'api_url' => env('GOLD_API_URL'),

    'api_key' => env('GOLD_API_KEY'),

    'unit' => env('GOLD_UNIT', 'g'), // g or oz

    'currency' => env('GOLD_CURRENCY', 'EUR'),

    'polling_interval' => env('GOLD_POLLING_INTERVAL', 60), // seconds
];
