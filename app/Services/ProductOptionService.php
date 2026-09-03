<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductOptionService
{
    public function index(Product $product)
    {
        return $product->options()
            ->with('values')
            ->orderBy('sort_order')
            ->get();
    }

    public function create(
        Product $product,
        array $data
    ): ProductOption {
        return DB::transaction(function () use (
            $product,
            $data
        ) {
            $option = $product->options()->create([
                'name' => $data['name'],
                'slug' => $data['slug']
                    ?? Str::slug($data['name']),
                'sort_order' =>
                    $data['sort_order'] ?? 0,
                'is_required' =>
                    $data['is_required'] ?? false,
            ]);

            return $option->load('values');
        });
    }

    public function update(
        ProductOption $option,
        array $data
    ): ProductOption {
        $option->update($data);

        return $option->fresh()
            ->load('values');
    }

    public function delete(
        ProductOption $option
    ): void {
        DB::transaction(function () use ($option) {
            if (
                $option->values()
                    ->whereHas('variants')
                    ->exists()
            ) {
                abort(
                    422,
                    'This option cannot be deleted because one or more of its values are used by product variants.'
                );
            }

            $option->delete();
        });
    }
}
