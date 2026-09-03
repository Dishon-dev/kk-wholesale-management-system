<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function paginate(
        int $perPage = 20
    ): LengthAwarePaginator {
        return Category::query()
            ->withCount('products')
            ->latest()
            ->paginate($perPage);
    }

    public function find(Category $category): Category
    {
        return $category->loadCount('products');
    }

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create([
                'name' => $data['name'],
                'slug' => $data['slug']
                    ?? Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $category->loadCount('products');
        });
    }

    public function update(
        Category $category,
        array $data
    ): Category {
        return DB::transaction(function () use (
            $category,
            $data
        ) {
            $category->update(
                collect($data)
                    ->only([
                        'name',
                        'slug',
                        'description',
                        'image_path',
                        'is_active',
                    ])
                    ->toArray()
            );

            return $category->fresh()
                ->loadCount('products');
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $category->delete();
        });
    }
}
