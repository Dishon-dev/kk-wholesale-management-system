<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branchOne = Branch::where(
            'branch_code',
            'BR001'
        )->firstOrFail();

        $storeOne = Store::where(
            'store_code',
            'ST001'
        )->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@kkwholesalers.co.ke'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'branch_id' => null,
                'store_id' => null,
            ]
        );

        $admin->syncRoles(['Super Admin']);

        $branchManager = User::updateOrCreate(
            ['email' => 'branch@kkwholesalers.co.ke'],
            [
                'name' => 'Branch Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branchOne->id,
                'store_id' => null,
                'is_active' => true,
            ]
        );

        $branchManager->syncRoles([
            'Branch Manager',
        ]);

        $storeManager = User::updateOrCreate(
            ['email' => 'store@kkwholesalers.co.ke'],
            [
                'name' => 'Store Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branchOne->id,
                'store_id' => $storeOne->id,
                'is_active' => true,
            ]
        );

        $storeManager->syncRoles([
            'Store Manager',
        ]);

        $customer = User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'branch_id' => null,
                'store_id' => null,
                'phone' => '+254700000200',
                'is_active' => true,
            ]
        );

        $customer->syncRoles([
            'Customer',
        ]);
    }
}
