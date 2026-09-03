<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function getOrCreate(
        Store $store,
        ProductVariant $variant,
        bool $lock = false
    ): Stock {
        $query = Stock::query()
            ->where('store_id', $store->id)
            ->where('product_variant_id', $variant->id);

        if ($lock) {
            $query->lockForUpdate();
        }

        $stock = $query->first();

        if ($stock) {
            return $stock;
        }

        return Stock::create([
            'store_id' => $store->id,
            'product_variant_id' => $variant->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'reorder_level' => 0,
        ]);
    }

    public function increase(
        Store $store,
        ProductVariant $variant,
        float $quantity,
        string $type,
        ?Model $reference,
        User $user,
        ?float $unitCost = null,
        ?string $notes = null
    ): Stock {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $store,
            $variant,
            $quantity,
            $type,
            $reference,
            $user,
            $unitCost,
            $notes
        ) {
            $stock = $this->getOrCreate(
                $store,
                $variant,
                true
            );

            $balanceBefore = (float) $stock->quantity;

            $stock->quantity =
                $balanceBefore + $quantity;

            $stock->save();

            StockMovement::create([
                'store_id' => $store->id,
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' =>
                    $unitCost ?? $variant->cost,
                'balance_before' => $balanceBefore,
                'balance_after' => $stock->quantity,
                'reference_type' =>
                    $reference?->getMorphClass(),
                'reference_id' =>
                    $reference?->getKey(),
                'created_by' => $user->id,
                'notes' => $notes,
            ]);

            return $stock->fresh([
                'store',
                'productVariant.product',
            ]);
        });
    }

    public function decrease(
        Store $store,
        ProductVariant $variant,
        float $quantity,
        string $type,
        ?Model $reference,
        User $user,
        ?string $notes = null
    ): Stock {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Quantity must be greater than zero.'
            );
        }

        if (! $variant->track_stock) {
            return $this->getOrCreate(
                $store,
                $variant
            );
        }

        return DB::transaction(function () use (
            $store,
            $variant,
            $quantity,
            $type,
            $reference,
            $user,
            $notes
        ) {
            $stock = $this->getOrCreate(
                $store,
                $variant,
                true
            );

            $balanceBefore = (float) $stock->quantity;

            if ($balanceBefore < $quantity) {
                throw new RuntimeException(
                    "Insufficient stock for SKU {$variant->sku}."
                );
            }

            $stock->quantity =
                $balanceBefore - $quantity;

            $stock->save();

            StockMovement::create([
                'store_id' => $store->id,
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => -$quantity,
                'unit_cost' => $variant->cost,
                'balance_before' => $balanceBefore,
                'balance_after' => $stock->quantity,
                'reference_type' =>
                    $reference?->getMorphClass(),
                'reference_id' =>
                    $reference?->getKey(),
                'created_by' => $user->id,
                'notes' => $notes,
            ]);

            return $stock->fresh([
                'store',
                'productVariant.product',
            ]);
        });
    }

    public function openingStock(
        Store $store,
        ProductVariant $variant,
        float $quantity,
        User $user,
        ?float $unitCost = null,
        ?string $notes = null
    ): Stock {
        if ($quantity < 0) {
            throw new RuntimeException(
                'Opening stock cannot be negative.'
            );
        }

        return DB::transaction(function () use (
            $store,
            $variant,
            $quantity,
            $user,
            $unitCost,
            $notes
        ) {
            $stock = $this->getOrCreate(
                $store,
                $variant,
                true
            );

            $balanceBefore = (float) $stock->quantity;

            $stock->quantity = $quantity;
            $stock->save();

            $movementQuantity =
                $quantity - $balanceBefore;

            if ($movementQuantity != 0) {
                StockMovement::create([
                    'store_id' => $store->id,
                    'product_variant_id' => $variant->id,
                    'type' => 'opening',
                    'quantity' => $movementQuantity,
                    'unit_cost' =>
                        $unitCost ?? $variant->cost,
                    'balance_before' =>
                        $balanceBefore,
                    'balance_after' =>
                        $stock->quantity,
                    'created_by' => $user->id,
                    'notes' => $notes,
                ]);
            }

            return $stock->fresh([
                'store',
                'productVariant.product',
            ]);
        });
    }
    
    public function adjust(
        Store $store,
        ProductVariant $variant,
        float $quantity,
        User $user,
        ?string $notes = null
    ): Stock {
        if ($quantity == 0) {
            throw new RuntimeException(
                'Adjustment quantity cannot be zero.'
            );
        }

        if ($quantity > 0) {
            return $this->increase(
                $store,
                $variant,
                $quantity,
                'adjustment_in',
                null,
                $user,
                null,
                $notes
            );
        }

        return $this->decrease(
            $store,
            $variant,
            abs($quantity),
            'adjustment_out',
            null,
            $user,
            $notes
        );
    }
}
