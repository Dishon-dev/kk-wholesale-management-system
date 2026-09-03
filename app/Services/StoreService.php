<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function paginate(
        int $perPage = 20,
        ?int $branchId = null
    ): LengthAwarePaginator {
        return Store::query()
            ->with('branch')
            ->withCount('users')
            ->when(
                $branchId,
                fn ($query) => $query->where(
                    'branch_id',
                    $branchId
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function find(Store $store): Store
    {
        return $store
            ->load('branch')
            ->loadCount('users');
    }

    public function create(array $data): Store
    {
        return DB::transaction(function () use ($data) {
            $store = Store::create([
                'branch_id' => $data['branch_id'],
                'name' => $data['name'],
                'store_code' => $data['store_code'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $store->load('branch')
                ->loadCount('users');
        });
    }

    public function update(
        Store $store,
        array $data
    ): Store {
        return DB::transaction(function () use (
            $store,
            $data
        ) {
            $store->update(
                collect($data)
                    ->only([
                        'branch_id',
                        'name',
                        'store_code',
                        'phone',
                        'email',
                        'address',
                        'is_active',
                    ])
                    ->toArray()
            );

            return $store->fresh()
                ->load('branch')
                ->loadCount('users');
        });
    }

    public function delete(Store $store): void
    {
        DB::transaction(function () use ($store) {
            $store->delete();
        });
    }
}
