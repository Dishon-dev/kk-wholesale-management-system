<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $variants = ProductVariant::where(
            'track_stock',
            true
        )->get();

        foreach ($stores as $store) {
            foreach ($variants as $variant) {
                $quantity = match ($variant->sku) {
                    'SAM-A55-BLK' => 20,
                    'SAM-A55-BLU' => 15,
                    'IPH15-BLK' => 10,
                    'HP-LAPTOP-001' => 12,
                    'LOG-MOUSE-001' => 50,
                    'HDMI-001' => 100,
                    default => 0,
                };
                
                if ($store->store_code === 'ST002') {
                    $quantity = (int) floor($quantity / 2);
                }

                if ($store->store_code === 'ST003') {
                    $quantity = (int) floor($quantity / 4);
                }

                Stock::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'reorder_level' => 5,
                    ]
                );
            }
        }
    }
}
