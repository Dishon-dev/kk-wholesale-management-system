<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['branch_code' => 'BR001'],
            [
                'name' => 'Branch One',
                'phone' => '+254700000001',
                'email' => 'branch1@kkwholesalers.co.ke',
                'address' => 'Nairobi',
            ]
        );

        Branch::updateOrCreate(
            ['branch_code' => 'BR002'],
            [
                'name' => 'Branch Two',
                'phone' => '+254700000002',
                'email' => 'branch2@kkwholesalers.co.ke',
                'address' => 'Kajiado',
            ]
        );
    }
}