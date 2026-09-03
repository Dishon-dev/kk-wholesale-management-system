<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.assign',
            'roles.delete',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            
            'branches.view',
            'branches.create',
            'branches.update',
            'branches.delete',

            'stores.view',
            'stores.create',
            'stores.update',
            'stores.delete',

            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            'brands.view',
            'brands.create',
            'brands.update',
            'brands.delete',

            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            'product_variants.view',
            'product_variants.create',
            'product_variants.update',
            'product_variants.delete',

            'product_options.view',
            'product_options.create',
            'product_options.update',
            'product_options.delete',

            'product_option_values.view',
            'product_option_values.create',
            'product_option_values.update',
            'product_option_values.delete',

            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'stock_movements.view',

            'sales.view',
            'sales.create',
            'sales.void',

            'payments.view',
            'payments.create',

            'returns.view',
            'returns.create',
            'returns.cancel',

            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $branchManager = Role::findOrCreate('Branch Manager', 'web');
        $storeManager = Role::findOrCreate('Store Manager', 'web');
        $customer = Role::findOrCreate('Customer', 'web');

        $superAdmin->syncPermissions(
            Permission::all()
        );

        $branchManager->syncPermissions([
            'branches.view',
            'stores.view',
            'stores.create',
            'stores.update',

            'users.view',
            'users.create',
            'users.update',

            'products.view',
            'products.create',
            'products.update',

            'categories.view',
            'categories.create',
            'categories.update',

            'brands.view',
            'brands.create',
            'brands.update',

            'inventory.view',
            'inventory.adjust',

            'sales.view',
            'sales.create',
            'sales.void',

            'payments.view',
            'payments.create',

            'returns.view',
            'returns.create',
            'returns.cancel',

            'reports.view',
        ]);

        $storeManager->syncPermissions([
            'stores.view',

            'users.view',

            'products.view',
            'products.create',
            'products.update',

            'categories.view',
            'brands.view',

            'inventory.view',
            'inventory.adjust',

            'sales.view',
            'sales.create',
            'sales.void',

            'payments.view',
            'payments.create',

            'returns.view',
            'returns.create',
            'returns.cancel',
            
            'reports.view',
        ]);

        $customer->syncPermissions([
            'products.view',
        ]);
    }
}
