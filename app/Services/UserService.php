<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected function assignRole(
        User $actor,
        User $user,
        string $role
    ): void {
        if (
            ! $actor->hasRole('Super Admin') &&
            ! $actor->can('roles.assign')
        ) {
            throw ValidationException::withMessages([
                'role' => [
                    'You do not have permission to assign roles.',
                ],
            ]);
        }
    
        $user->syncRoles([$role]);
    }

    /**
     * Get users.
     */
    public function paginate(
        int $perPage = 20
    ): LengthAwarePaginator {
        return User::query()
            ->with([
                'branch',
                'store',
                'stores',
                'roles',
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a user.
     */
    public function find(
        User $user
    ): User {
        return $user->load([
            'branch',
            'store',
            'stores',
            'roles',
            'permissions',
        ]);
    }

    /**
     * Create user.
     */
    public function create(
        array $data
    ): User {
        return DB::transaction(
            function () use ($data) {

                $user = User::create([
                    'name' =>
                        $data['name'],

                    'email' =>
                        $data['email'],

                    'phone' =>
                        $data['phone'] ?? null,

                    'password' =>
                        Hash::make(
                            $data['password']
                        ),

                    'branch_id' =>
                        $data['branch_id'] ?? null,

                    'store_id' =>
                        $data['store_id'] ?? null,

                    'is_active' =>
                        $data['is_active'] ?? true,
                ]);

                if (! empty($data['role'])) {
                    $user->assignRole($data['role']);

                    // $this->assignRole(auth()->user(), $user, $data['role']);
                }

                if (
                    array_key_exists(
                        'store_ids',
                        $data
                    )
                ) {
                    $user->stores()->sync(
                        $data['store_ids'] ?? []
                    );
                }

                return $user->load([
                    'branch',
                    'store',
                    'stores',
                    'roles',
                    'permissions',
                ]);
            }
        );
    }

    /**
     * Update user.
     */
    public function update(
        User $user,
        array $data
    ): User {
        return DB::transaction(
            function () use (
                $user,
                $data
            ) {

                $updateData = collect($data)
                    ->only([
                        'name',
                        'email',
                        'phone',
                        'branch_id',
                        'store_id',
                        'is_active',
                    ])
                    ->toArray();

                if (
                    ! empty(
                        $data['password'] ?? null
                    )
                ) {
                    $updateData['password'] =
                        Hash::make(
                            $data['password']
                        );
                }

                $user->update($updateData);

                if (
                    array_key_exists(
                        'role',
                        $data
                    )
                ) {
                    $user->syncRoles([
                        $data['role'],
                    ]);
                }

                if (
                    array_key_exists(
                        'store_ids',
                        $data
                    )
                ) {
                    $user->stores()->sync(
                        $data['store_ids'] ?? []
                    );
                }

                return $user->fresh([
                    'branch',
                    'store',
                    'stores',
                    'roles',
                    'permissions',
                ]);
            }
        );
    }

    public function delete(
        User $user
    ): void {
        $user->tokens()->delete();

        $user->stores()->detach();

        $user->delete();
    }
}