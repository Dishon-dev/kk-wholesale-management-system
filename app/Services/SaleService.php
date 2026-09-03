<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SaleService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected StoreAccessService $storeAccessService
    ) {}

    public function create(
        User $user,
        Store $store,
        array $data
    ): Sale {

        $this->storeAccessService->ensureAccess(
            $user,
            $store
        );

        return DB::transaction(
            function () use (
                $user,
                $store,
                $data
            ) {

                $subtotal = 0;

                $preparedItems = [];

                foreach ($data['items'] as $item) {

                    $variant = ProductVariant::query()
                        ->whereKey(
                            $item['product_variant_id']
                        )
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $quantity =
                        (float) $item['quantity'];

                    $unitPrice =
                        (float) $variant->price;

                    $lineSubtotal =
                        $quantity * $unitPrice;

                    $subtotal +=
                        $lineSubtotal;

                    $preparedItems[] = [
                        'variant' =>
                            $variant,

                        'quantity' =>
                            $quantity,

                        'unit_price' =>
                            $unitPrice,

                        'subtotal' =>
                            $lineSubtotal,
                    ];
                }

                $discount =
                    (float) (
                        $data['discount'] ?? 0
                    );

                $tax =
                    (float) (
                        $data['tax'] ?? 0
                    );

                $total =
                    $subtotal
                    - $discount
                    + $tax;

                if ($total < 0) {
                    throw new RuntimeException(
                        'Sale total cannot be negative.'
                    );
                }

                $sale = Sale::create([
                    'reference' =>
                        $this->generateReference(),

                    'store_id' =>
                        $store->id,

                    'customer_id' =>
                        $data['customer_id']
                        ?? null,

                    'created_by' =>
                        $user->id,

                    'sale_date' =>
                        now(),

                    'subtotal' =>
                        $subtotal,

                    'discount' =>
                        $discount,

                    'tax' =>
                        $tax,

                    'total' =>
                        $total,

                    'paid_amount' =>
                        0,

                    'balance_due' =>
                        $total,

                    'change_amount' =>
                        0,

                    'payment_status' =>
                        'unpaid',

                    'status' =>
                        'completed',

                    'notes' =>
                        $data['notes'] ?? null,
                ]);

                foreach ($preparedItems as $item) {

                    $variant =
                        $item['variant'];

                    $sale->items()->create([
                        'product_variant_id' =>
                            $variant->id,

                        'quantity' =>
                            $item['quantity'],

                        'unit_price' =>
                            $item['unit_price'],

                        'unit_cost' =>
                            $variant->cost,

                        'discount' =>
                            0,

                        'tax' =>
                            0,

                        'subtotal' =>
                            $item['subtotal'],

                        'total' =>
                            $item['subtotal'],
                    ]);

                    if ($variant->track_stock) {

                        $this->inventoryService
                            ->decrease(
                                $store,
                                $variant,
                                $item['quantity'],
                                'sale',
                                $sale,
                                $user
                            );
                    }
                }

                return $sale->fresh([
                    'items.productVariant.product',
                    'payments',
                    'customer',
                    'createdBy',
                    'store',
                ]);
            }
        );
    }

    protected function generateReference(): string
    {
        do {
            $reference =
                'SALE-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(
                    Str::random(5)
                );

        } while (
            Sale::where(
                'reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}