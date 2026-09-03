<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $galaxy = Product::where(
            'slug',
            'samsung-galaxy-a55'
        )->firstOrFail();

        $iphone = Product::where(
            'slug',
            'iphone-15'
        )->firstOrFail();

        $laptop = Product::where(
            'slug',
            'hp-laptop'
        )->firstOrFail();

        $mouse = Product::where(
            'slug',
            'logitech-wireless-mouse'
        )->firstOrFail();

        $hdmi = Product::where(
            'slug',
            'hdmi-cable'
        )->firstOrFail();

        $variants = [
            [
                'product_id' => $galaxy->id,
                'name' => 'Black',
                'sku' => 'SAM-A55-BLK',
                'price' => 45000,
                'cost' => 38000,
                'track_stock' => true,
                'is_default' => true,
            ],

            [
                'product_id' => $galaxy->id,
                'name' => 'Blue',
                'sku' => 'SAM-A55-BLU',
                'price' => 45000,
                'cost' => 38000,
                'track_stock' => true,
                'is_default' => false,
            ],

            [
                'product_id' => $iphone->id,
                'name' => 'Black',
                'sku' => 'IPH15-BLK',
                'price' => 115000,
                'cost' => 98000,
                'track_stock' => true,
                'is_default' => true,
            ],

            [
                'product_id' => $laptop->id,
                'name' => 'Default',
                'sku' => 'HP-LAPTOP-001',
                'price' => 85000,
                'cost' => 72000,
                'track_stock' => true,
                'is_default' => true,
            ],

            [
                'product_id' => $mouse->id,
                'name' => 'Default',
                'sku' => 'LOG-MOUSE-001',
                'price' => 3500,
                'cost' => 2200,
                'track_stock' => true,
                'is_default' => true,
            ],

            [
                'product_id' => $hdmi->id,
                'name' => 'Default',
                'sku' => 'HDMI-001',
                'price' => 1500,
                'cost' => 800,
                'track_stock' => true,
                'is_default' => true,
            ],
        ];

        foreach ($variants as $data) {
            ProductVariant::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        }
    }
}
