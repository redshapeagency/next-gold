<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CategorySeeder::class,
            StoreSettingSeeder::class,
        ]);

        // Solo in ambiente locale, crea dati demo
        if (app()->environment('local')) {
            $this->call([
                DemoSeeder::class,
            ]);
        }
    }
}
