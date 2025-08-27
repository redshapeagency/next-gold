<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Clients
            'view clients',
            'create clients', 
            'edit clients',
            'delete clients',

            // Items
            'view items',
            'create items',
            'edit items', 
            'delete items',

            // Documents
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'confirm documents',

            // Archive
            'view archive',
            'restore items',

            // Settings
            'access settings',
            'manage users',
            'manage store settings',
            'manage gold price',
            'view logs',
            'backup data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $operatorRole = Role::create(['name' => 'operator']);
        $operatorRole->givePermissionTo([
            'view clients', 'create clients', 'edit clients',
            'view items', 'create items', 'edit items',
            'view documents', 'create documents', 'edit documents', 'confirm documents',
            'view archive', 'restore items',
        ]);

        $viewerRole = Role::create(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'view clients',
            'view items', 
            'view documents',
            'view archive',
        ]);
    }
}
