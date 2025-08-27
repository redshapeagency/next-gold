<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        StoreSetting::create([
            'business_name' => 'Next Gold',
            'vat_number' => '',
            'tax_code' => '',
            'address' => '',
            'city' => '',
            'zip' => '',
            'country' => 'Italia',
            'phone' => '',
            'email' => '',
            'logo_path' => null,
            'doc_number_counters' => [],
            'currency' => 'EUR',
            'locale' => 'it_IT',
        ]);
    }
}
