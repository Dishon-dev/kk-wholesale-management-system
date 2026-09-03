<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandService
{
    public function paginate(
        int $perPage = 20
    ): LengthAwarePaginator {
        return Brand::query()
            ->withCount('products')
            ->latest()
            ->paginate($perPage);
    }

    public function find(Brand $brand): Brand
    {
        return $brand->loadCount('products');
    }

    public function create(array $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $brand = Brand::create([
                'name' => $data['name'],
                'slug' => $data['slug']
                    ?? Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'featured' => $data['featured'] ?? true,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $brand->loadCount('products');
        });
    }

    public function update(
        Brand $brand,
        array $data
    ): Brand {
        return DB::transaction(function () use (
            $brand,
            $data
        ) {
            $brand->update(
                collect($data)
                    ->only([
                        'name',
                        'slug',
                        'description',
                        'featured',
                        'is_active',
                    ])
                    ->toArray()
            );

            return $brand->fresh()
                ->loadCount('products');
        });
    }

    public function delete(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {
            $brand->delete();
        });
    }
}
