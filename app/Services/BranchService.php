<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BranchService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Branch::query()
            ->withCount('stores')
            ->latest()
            ->paginate($perPage);
    }

    public function find(Branch $branch): Branch
    {
        return $branch->loadCount('stores');
    }

    public function create(array $data): Branch
    {
        return DB::transaction(function () use ($data) {
            $branch = Branch::create([
                'name' => $data['name'],
                'branch_code' => $data['branch_code'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $branch->loadCount('stores');
        });
    }

    public function update(Branch $branch, array $data): Branch
    {
        return DB::transaction(function () use ($branch, $data) {
            $branch->update(
                collect($data)
                    ->only([
                        'name',
                        'branch_code',
                        'phone',
                        'email',
                        'address',
                        'is_active',
                    ])
                    ->toArray()
            );

            return $branch->fresh()->loadCount('stores');
        });
    }

    public function delete(Branch $branch): void
    {
        DB::transaction(function () use ($branch) {
            $branch->delete();
        });
    }
}