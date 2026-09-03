<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class StoreAccessService
{
    public function ensureAccess(
        User $user,
        Store $store
    ): void {
        if ($user->hasRole('Super Admin')) {
            return;
        }

        if (
            $user->store_id === $store->id
            ||
            $user->stores()
                ->whereKey($store->id)
                ->exists()
        ) {
            return;
        }

        throw new AuthorizationException(
            'You do not have access to this store.'
        );
    }
}