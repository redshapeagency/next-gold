<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Client;
use App\Models\Item;
use App\Models\Document;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view dashboard',
            'manage clients',
            'manage items',
            'manage documents',
            'manage archive',
            'manage settings',
            'manage users',
            'view logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions);
        $operatorRole->syncPermissions(['view dashboard', 'manage clients', 'manage items', 'manage documents', 'manage archive']);
        $viewerRole->syncPermissions(['view dashboard']);

        // Create demo user if in local environment
        if (app()->environment('local')) {
            $user = User::firstOrCreate([
                'email' => 'admin@nextgold.com'
            ], [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('admin');

            // Create demo categories
            $categories = ['Anelli', 'Collane', 'Orecchini', 'Bracciali', 'Monete'];
            foreach ($categories as $category) {
                Category::firstOrCreate(['name' => $category]);
            }

            // Create demo clients
            Client::factory(10)->create();

            // Create demo items
            Item::factory(20)->create();

            // Create demo documents
            Document::factory(5)->create();
        }

        // Create store settings
        StoreSetting::firstOrCreate([], [
            'business_name' => 'Compro Oro Next Gold',
            'currency' => 'EUR',
            'locale' => 'it_IT',
            'doc_number_counters' => [
                '2024' => [
                    'sale' => 0,
                    'purchase' => 0,
                ],
            ],
        ]);
    }
}
