<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SaleReturnService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected StoreAccessService $storeAccessService
    ) {}

    public function create(
        User $user,
        Sale $sale,
        Store $store,
        array $data
    ): SaleReturn {
        $this->storeAccessService
            ->ensureAccess($user, $store);

        if ($sale->store_id !== $store->id) {
            throw new RuntimeException(
                'The sale does not belong to this store.'
            );
        }

        if ($sale->status !== 'completed') {
            throw new RuntimeException(
                'Only completed sales can be returned.'
            );
        }

        return DB::transaction(function () use (
            $user,
            $sale,
            $store,
            $data
        ) {
            $sale = Sale::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($sale->id);

            $subtotal = 0;

            $preparedItems = [];

            foreach ($data['items'] as $item) {
                $variantId =
                    $item['product_variant_id'];

                $quantity =
                    (float) $item['quantity'];

                $saleItem = $sale->items
                    ->firstWhere(
                        'product_variant_id',
                        $variantId
                    );

                if (! $saleItem) {
                    throw new RuntimeException(
                        'The product variant was not part of this sale.'
                    );
                }

                $alreadyReturned =
                    $this->returnedQuantity(
                        $sale,
                        $variantId
                    );

                $soldQuantity =
                    (float) $saleItem->quantity;

                $remaining =
                    $soldQuantity
                    - $alreadyReturned;

                if ($quantity > $remaining) {
                    throw new RuntimeException(
                        "Return quantity for SKU {$saleItem->productVariant->sku} "
                        . "cannot exceed the remaining sold quantity."
                    );
                }

                $unitPrice =
                    (float) $saleItem->unit_price;

                $lineTotal =
                    $quantity * $unitPrice;

                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'sale_item' => $saleItem,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                    'reason' =>
                        $item['reason'] ?? null,
                ];
            }

            $tax = 0;

            $total =
                $subtotal + $tax;

            $return = SaleReturn::create([
                'reference' =>
                    $this->generateReference(),

                'sale_id' =>
                    $sale->id,

                'store_id' =>
                    $store->id,

                'created_by' =>
                    $user->id,

                'return_date' =>
                    now(),

                'subtotal' =>
                    $subtotal,

                'tax' =>
                    $tax,

                'total' =>
                    $total,

                'status' =>
                    'completed',

                'reason' =>
                    $data['reason'] ?? null,
            ]);

            foreach ($preparedItems as $item) {
                $return->items()->create([
                    'product_variant_id' =>
                        $item['variant_id'],

                    'quantity' =>
                        $item['quantity'],

                    'unit_price' =>
                        $item['unit_price'],

                    'total' =>
                        $item['total'],

                    'reason' =>
                        $item['reason'],
                ]);

                $variant =
                    ProductVariant::findOrFail(
                        $item['variant_id']
                    );

                if ($variant->track_stock) {
                    $this->inventoryService->increase(
                        $store,
                        $variant,
                        $item['quantity'],
                        'return',
                        $return,
                        $user,
                        $variant->cost,
                        $item['reason']
                    );
                }
            }

            return $return->fresh([
                'sale',
                'store',
                'createdBy',
                'items.productVariant.product',
            ]);
        });
    }

    protected function returnedQuantity(
        Sale $sale,
        int $variantId
    ): float {
        return (float) SaleReturn::query()
            ->where('sale_id', $sale->id)
            ->where('status', 'completed')
            ->with('items')
            ->get()
            ->flatMap(
                fn ($return) => $return->items
            )
            ->where(
                'product_variant_id',
                $variantId
            )
            ->sum(
                fn ($item) => (float) $item->quantity
            );
    }

    protected function generateReference(): string
    {
        do {
            $reference =
                'RET-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(
                    Str::random(5)
                );

        } while (
            SaleReturn::where(
                'reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}
