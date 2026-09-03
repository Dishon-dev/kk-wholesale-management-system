<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where(
            'slug',
            'electronics'
        )->firstOrFail();

        $computers = Category::where(
            'slug',
            'computers'
        )->firstOrFail();

        $phones = Category::where(
            'slug',
            'mobile-phones'
        )->firstOrFail();

        $accessories = Category::where(
            'slug',
            'accessories'
        )->firstOrFail();

        $samsung = Brand::where(
            'slug',
            'samsung'
        )->firstOrFail();

        $apple = Brand::where(
            'slug',
            'apple'
        )->firstOrFail();

        $hp = Brand::where(
            'slug',
            'hp'
        )->firstOrFail();

        $logitech = Brand::where(
            'slug',
            'logitech'
        )->firstOrFail();

        $products = [
            [
                'name' => 'Samsung Galaxy A55',
                'category_id' => $phones->id,
                'brand_id' => $samsung->id,
                'short_description' => 'Samsung Galaxy A55 smartphone.',
                'description' => 'Demo product for the inventory system.',
                'currency' => 'KES',
                'is_featured' => true,
                'is_bestseller' => true,
            ],

            [
                'name' => 'iPhone 15',
                'category_id' => $phones->id,
                'brand_id' => $apple->id,
                'short_description' => 'Apple iPhone 15.',
                'description' => 'Demo product for the inventory system.',
                'currency' => 'KES',
                'is_featured' => true,
                'is_bestseller' => true,
            ],

            [
                'name' => 'HP Laptop',
                'category_id' => $computers->id,
                'brand_id' => $hp->id,
                'short_description' => 'HP business laptop.',
                'description' => 'Demo product for the inventory system.',
                'currency' => 'KES',
                'is_featured' => false,
                'is_bestseller' => true,
            ],

            [
                'name' => 'Logitech Wireless Mouse',
                'category_id' => $accessories->id,
                'brand_id' => $logitech->id,
                'short_description' => 'Wireless computer mouse.',
                'description' => 'Demo product for the inventory system.',
                'currency' => 'KES',
                'is_featured' => false,
                'is_bestseller' => false,
            ],

            [
                'name' => 'HDMI Cable',
                'category_id' => $electronics->id,
                'brand_id' => null,
                'short_description' => 'High-speed HDMI cable.',
                'description' => 'Demo product for the inventory system.',
                'currency' => 'KES',
                'is_featured' => false,
                'is_bestseller' => false,
            ],
        ];

        foreach ($products as $data) {
            $slug = Str::slug($data['name']);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    ...$data,
                    'slug' => $slug,
                    'is_active' => true,
                ]
            );
        }
    }
}
