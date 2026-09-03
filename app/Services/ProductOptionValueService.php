<?php

namespace App\Services;

use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductOptionValueService
{
    public function index(ProductOption $option)
    {
        return $option->values()
            ->orderBy('sort_order')
            ->get();
    }

    public function create(
        ProductOption $option,
        array $data
    ): ProductOptionValue {
        return DB::transaction(function () use (
            $option,
            $data
        ) {
            return $option->values()->create([
                'value' => $data['value'],
                'slug' => $data['slug']
                    ?? Str::slug($data['value']),
                'sort_order' =>
                    $data['sort_order'] ?? 0,
            ]);
        });
    }

    public function update(
        ProductOptionValue $value,
        array $data
    ): ProductOptionValue {
        $value->update($data);

        return $value->fresh();
    }

    public function delete(
        ProductOptionValue $value
    ): void {
        DB::transaction(function () use ($value) {

            if ($value->variants()->exists()) {
                abort(
                    422,
                    'This option value cannot be deleted because it is used by a product variant.'
                );
            }

            $value->delete();
        });
    }
}
