<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function paginate(
        Product $product,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $product->variants()
            ->with([
                'optionValues.option',
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function find(
        Product $product,
        ProductVariant $variant
    ): ProductVariant {
        return $variant->load([
            'product',
            'optionValues.option',
        ]);
    }

    public function create(
        Product $product,
        array $data
    ): ProductVariant {
        return DB::transaction(function () use (
            $product,
            $data
        ) {
            if (
                ($data['is_default'] ?? false)
                === true
            ) {
                $product->variants()
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $variant = $product->variants()->create([
                'name' =>
                    $data['name'],
                'sku' =>
                    $data['sku'],
                'barcode' =>
                    $data['barcode'] ?? null,
                'price' =>
                    $data['price'],
                'cost' =>
                    $data['cost'] ?? null,
                'weight_kg' =>
                    $data['weight_kg'] ?? null,
                'dimensions_cm' =>
                    $data['dimensions_cm'] ?? null,
                'track_stock' =>
                    $data['track_stock'] ?? true,
                'is_default' =>
                    $data['is_default'] ?? false,
                'is_active' =>
                    $data['is_active'] ?? true,
            ]);

            $optionValueIds =
                $data['option_value_ids'] ?? [];

            if (! empty($optionValueIds)) {

                $this->validateOptionValuesBelongToProduct(
                    $product,
                    $optionValueIds
                );

                $this->validateOneValuePerOption(
                    $optionValueIds
                );

                $variant->optionValues()->sync(
                    $optionValueIds
                );
            }

            return $this->find(
                $product,
                $variant
            );
        });
    }

    public function update(
        Product $product,
        ProductVariant $variant,
        array $data
    ): ProductVariant {
        return DB::transaction(function () use (
            $product,
            $variant,
            $data
        ) {
            if (
                ($data['is_default'] ?? false)
                === true
            ) {
                $product->variants()
                    ->whereKey('!=', $variant->id)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $variant->update(
                collect($data)
                    ->only([
                        'name',
                        'sku',
                        'barcode',
                        'price',
                        'cost',
                        'weight_kg',
                        'dimensions_cm',
                        'track_stock',
                        'is_default',
                        'is_active',
                    ])
                    ->toArray()
            );

            if (
                array_key_exists(
                    'option_value_ids',
                    $data
                )
            ) {
                $optionValueIds =
                    $data['option_value_ids'] ?? [];

                $this->validateOptionValuesBelongToProduct(
                    $product,
                    $optionValueIds
                );

                $this->validateOneValuePerOption(
                    $optionValueIds
                );

                $variant->optionValues()->sync(
                    $optionValueIds
                );
            }

            return $this->find(
                $product,
                $variant->fresh()
            );
        });
    }

    public function delete(
        Product $product,
        ProductVariant $variant
    ): void {
        DB::transaction(function () use (
            $product,
            $variant
        ) {
            if ($variant->is_default) {
                abort(
                    422,
                    'The default variant cannot be deleted. Assign another default variant first.'
                );
            }

            $variant->optionValues()->detach();

            $variant->delete();
        });
    }

    protected function validateOptionValuesBelongToProduct(
        Product $product,
        array $optionValueIds
    ): void {
        if (empty($optionValueIds)) {
            return;
        }

        $validCount = $product->options()
            ->whereHas(
                'values',
                fn ($query) =>
                    $query->whereIn(
                        'product_option_values.id',
                        $optionValueIds
                    )
            )
            ->join(
                'product_option_values',
                'product_options.id',
                '=',
                'product_option_values.product_option_id'
            )
            ->whereIn(
                'product_option_values.id',
                $optionValueIds
            )
            ->distinct(
                'product_option_values.id'
            )
            ->count(
                'product_option_values.id'
            );

        if (
            $validCount !== count(
                array_unique($optionValueIds)
            )
        ) {
            abort(
                422,
                'One or more option values do not belong to this product.'
            );
        }
    }

    protected function validateOneValuePerOption(
        array $optionValueIds
    ): void {
        if (empty($optionValueIds)) {
            return;
        }

        $values = \App\Models\ProductOptionValue::query()
            ->with('option')
            ->whereIn('id', $optionValueIds)
            ->get();

        $duplicateOptions = $values
            ->groupBy('product_option_id')
            ->filter(
                fn ($group) => $group->count() > 1
            );

        if ($duplicateOptions->isNotEmpty()) {
            abort(
                422,
                'A variant can only have one value for each product option.'
            );
        }
    }
}