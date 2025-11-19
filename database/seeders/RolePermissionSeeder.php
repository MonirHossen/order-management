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
            // Product permissions
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'manage-inventory',
            
            // Order permissions
            'view-orders',
            'create-orders',
            'edit-orders',
            'cancel-orders',
            'view-all-orders',
            
            // User permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Report permissions
            'view-reports',
            'export-data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - full access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Vendor role
        $vendor = Role::create(['name' => 'vendor']);
        $vendor->givePermissionTo([
            'view-products',
            'create-products',
            'edit-products',
            'manage-inventory',
            'view-orders',
            'edit-orders',
            'view-reports',
        ]);

        // Customer role
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view-products',
            'create-orders',
            'view-orders',
            'cancel-orders',
        ]);
    }
}