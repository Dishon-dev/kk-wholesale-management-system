<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Database\Seeder;

class ProductOptionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            if (
                in_array(
                    $product->slug,
                    [
                        'samsung-galaxy-a55',
                        'iphone-15',
                    ],
                    true
                )
            ) {
                ProductOption::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'slug' => 'color',
                    ],
                    [
                        'name' => 'Color',
                    ]
                );
            }
        }
    }
}
