<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $branchOne = Branch::where(
            'branch_code',
            'BR001'
        )->firstOrFail();

        $branchTwo = Branch::where(
            'branch_code',
            'BR002'
        )->firstOrFail();

        Store::updateOrCreate(
            ['store_code' => 'ST001'],
            [
                'branch_id' => $branchOne->id,
                'name' => 'Store One',
                'phone' => '+254700000101',
                'email' => 'store1@example.com',
                'address' => 'Nairobi',
                'is_active' => true,
            ]
        );

        Store::updateOrCreate(
            ['store_code' => 'ST002'],
            [
                'branch_id' => $branchTwo->id,
                'name' => 'Store Two',
                'phone' => '+254700000102',
                'email' => 'store2@example.com',
                'address' => 'Mombasa',
                'is_active' => true,
            ]
        );

        Store::updateOrCreate(
            ['store_code' => 'ST003'],
            [
                'branch_id' => $branchTwo->id,
                'name' => 'Store Three',
                'phone' => '+254700000103',
                'email' => 'store3@example.com',
                'address' => 'Mombasa',
                'is_active' => true,
            ]
        );
    }
}
