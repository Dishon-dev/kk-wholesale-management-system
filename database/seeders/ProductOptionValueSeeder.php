<?php

namespace Database\Seeders;

use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductOptionValueSeeder extends Seeder
{
    public function run(): void
    {
        $colorOptions = ProductOption::query()
            ->where('slug', 'color')
            ->get();

        foreach ($colorOptions as $option) {
            foreach ([
                'Black',
                'Blue',
                'White',
                'Pink',
            ] as $value) {
                ProductOptionValue::updateOrCreate(
                    [
                        'product_option_id' => $option->id,
                        'slug' => Str::slug($value),
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }
}
