<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function paginate(
        int $perPage = 20,
        ?int $categoryId = null,
        ?int $brandId = null,
        ?string $search = null
    ): LengthAwarePaginator {
        return Product::query()
            ->with([
                'category',
                'brand',
                'variants',
                'defaultVariant',
            ])
            ->when(
                $categoryId,
                fn ($query) => $query->where(
                    'category_id',
                    $categoryId
                )
            )
            ->when(
                $brandId,
                fn ($query) => $query->where(
                    'brand_id',
                    $brandId
                )
            )
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })
            )
            ->latest()
            ->paginate($perPage);
    }

    public function find(Product $product): Product
    {
        return $product->load([
            'category',
            'brand',
            'options.values',
            'variants.optionValues.option',
            'defaultVariant',
        ]);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            $product = Product::create([
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $data['slug']
                    ?? Str::slug($data['name']),
                'short_description' =>
                    $data['short_description'] ?? null,
                'description' =>
                    $data['description'] ?? null,
                'currency' =>
                    $data['currency'] ?? 'KES',
                'weight_kg' =>
                    $data['weight_kg'] ?? null,
                'dimensions_cm' =>
                    $data['dimensions_cm'] ?? null,
                'gallery' =>
                    $data['gallery'] ?? null,
                'is_featured' =>
                    $data['is_featured'] ?? false,
                'is_bestseller' =>
                    $data['is_bestseller'] ?? false,
                'is_active' =>
                    $data['is_active'] ?? true,
                'meta_title' =>
                    $data['meta_title'] ?? null,
                'meta_description' =>
                    $data['meta_description'] ?? null,
            ]);

            $optionValueMap = [];

            foreach ($data['options'] ?? [] as $optionData) {

                $option = $product->options()->create([
                    'name' => $optionData['name'],
                    'slug' => $optionData['slug']
                        ?? Str::slug($optionData['name']),
                    'sort_order' =>
                        $optionData['sort_order'] ?? 0,
                    'is_required' =>
                        $optionData['is_required'] ?? false,
                ]);

                foreach (
                    $optionData['values']
                    as $valueData
                ) {
                    $value = $option->values()->create([
                        'value' => $valueData['value'],
                        'slug' => $valueData['slug']
                            ?? Str::slug($valueData['value']),
                        'sort_order' =>
                            $valueData['sort_order'] ?? 0,
                    ]);

                    $optionValueMap[$value->id] = $value;
                }
            }

            if (empty($data['variants'])) {

                $variant = $product->variants()->create([
                    'name' => 'Default',
                    'sku' => $data['sku'],
                    'barcode' =>
                        $data['barcode'] ?? null,
                    'price' => $data['price'],
                    'cost' =>
                        $data['cost'] ?? null,
                    'track_stock' =>
                        $data['track_stock'] ?? true,
                    'is_default' => true,
                    'is_active' => true,
                ]);

            } else {

                $defaultAssigned = false;

                foreach (
                    $data['variants']
                    as $index => $variantData
                ) {
                    $isDefault =
                        $variantData['is_default']
                        ?? false;

                    if (
                        $index === 0
                        && ! $defaultAssigned
                        && ! collect($data['variants'])
                            ->contains(
                                fn ($item) =>
                                    ($item['is_default'] ?? false) === true
                            )
                    ) {
                        $isDefault = true;
                    }

                    if ($isDefault) {
                        $defaultAssigned = true;
                    }

                    $variant = $product->variants()->create([
                        'name' =>
                            $variantData['name'],
                        'sku' =>
                            $variantData['sku'],
                        'barcode' =>
                            $variantData['barcode'] ?? null,
                        'price' =>
                            $variantData['price'],
                        'cost' =>
                            $variantData['cost'] ?? null,
                        'weight_kg' =>
                            $variantData['weight_kg'] ?? null,
                        'dimensions_cm' =>
                            $variantData['dimensions_cm'] ?? null,
                        'track_stock' =>
                            $variantData['track_stock']
                            ?? true,
                        'is_default' =>
                            $isDefault,
                        'is_active' =>
                            $variantData['is_active']
                            ?? true,
                    ]);

                    $optionValueIds =
                        $variantData['option_value_ids']
                        ?? [];

                    if (! empty($optionValueIds)) {
                        $this->validateOptionValuesBelongToProduct(
                            $product,
                            $optionValueIds
                        );

                        $variant->optionValues()->sync(
                            $optionValueIds
                        );
                    }
                }
            }

            return $this->find($product);
        });
    }

    public function update(
        Product $product,
        array $data
    ): Product {
        return DB::transaction(function () use (
            $product,
            $data
        ) {
            $product->update(
                collect($data)
                    ->only([
                        'category_id',
                        'brand_id',
                        'name',
                        'slug',
                        'short_description',
                        'description',
                        'currency',
                        'weight_kg',
                        'dimensions_cm',
                        'gallery',
                        'is_featured',
                        'is_bestseller',
                        'is_active',
                        'meta_title',
                        'meta_description',
                    ])
                    ->toArray()
            );

            return $this->find($product->fresh());
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->delete();
        });
    }

    protected function validateOptionValuesBelongToProduct(
        Product $product,
        array $optionValueIds
    ): void {
        $count = $product->options()
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
            ->count();

        if ($count !== count(array_unique($optionValueIds))) {
            abort(422, 'One or more option values do not belong to this product.');
        }
    }
}
