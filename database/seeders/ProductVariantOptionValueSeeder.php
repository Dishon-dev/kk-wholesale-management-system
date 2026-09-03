<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\ProductOptionValue;
use Illuminate\Database\Seeder;

class ProductVariantOptionValueSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            'SAM-A55-BLK' => 'black',
            'SAM-A55-BLU' => 'blue',
            'IPH15-BLK' => 'black',
        ];

        foreach ($mappings as $sku => $valueSlug) {
            $variant = ProductVariant::where(
                'sku',
                $sku
            )->first();

            if (! $variant) {
                continue;
            }

            $value = ProductOptionValue::where(
                'slug',
                $valueSlug
            )->whereHas(
                'productOption',
                fn ($query) => $query->where(
                    'product_id',
                    $variant->product_id
                )
            )->first();

            if ($value) {
                $variant->optionValues()->syncWithoutDetaching([
                    $value->id,
                ]);
            }
        }
    }
}
