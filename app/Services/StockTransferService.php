<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class StockTransferService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected StoreAccessService $storeAccessService
    ) {}

    public function create(
        User $user,
        array $data
    ): StockTransfer {
        $fromStore = Store::query()
            ->findOrFail($data['from_store_id']);

        $toStore = Store::query()
            ->findOrFail($data['to_store_id']);

        if ($fromStore->id === $toStore->id) {
            throw new RuntimeException(
                'Source and destination stores must be different.'
            );
        }

        $this->storeAccessService->ensureAccess(
            $user,
            $fromStore
        );

        $this->storeAccessService->ensureAccess(
            $user,
            $toStore
        );

        return DB::transaction(function () use (
            $user,
            $data,
            $fromStore,
            $toStore
        ) {
            $transfer = StockTransfer::create([
                'reference' => $this->generateReference(),
                'from_store_id' => $fromStore->id,
                'to_store_id' => $toStore->id,
                'created_by' => $user->id,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::query()
                    ->whereKey($item['product_variant_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = (float) $item['quantity'];

                if (! $variant->track_stock) {
                    throw new RuntimeException(
                        "SKU {$variant->sku} does not track stock and cannot be transferred."
                    );
                }

                $transfer->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);

                $this->inventoryService->decrease(
                    $fromStore,
                    $variant,
                    $quantity,
                    'transfer_out',
                    $transfer,
                    $user
                );
                
                $this->inventoryService->increase(
                    $toStore,
                    $variant,
                    $quantity,
                    'transfer_in',
                    $transfer,
                    $user
                );
            }

            return $transfer->fresh([
                'fromStore',
                'toStore',
                'createdBy',
                'items.productVariant.product',
            ]);
        });
    }

    protected function generateReference(): string
    {
        do {
            $reference =
                'TRF-' .
                now()->format('YmdHis') .
                '-' .
                strtoupper(Str::random(5));

        } while (
            StockTransfer::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
