<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $availableQuantity = (float) $this->quantity
            - (float) $this->reserved_quantity;

        return [
            'id' => $this->id,

            'store' => $this->whenLoaded('store', function () {
                return [
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'store_code' => $this->store->store_code,
                ];
            }),

            'product_variant' => $this->whenLoaded(
                'productVariant',
                function () {
                    return [
                        'id' => $this->productVariant->id,
                        'name' => $this->productVariant->name,
                        'sku' => $this->productVariant->sku,
                        'barcode' => $this->productVariant->barcode,
                        'price' => $this->productVariant->price,
                        'track_stock' => $this->productVariant->track_stock,

                        'product' => $this->productVariant->relationLoaded('product')
                            ? [
                                'id' => $this->productVariant->product->id,
                                'name' => $this->productVariant->product->name,
                                'slug' => $this->productVariant->product->slug,
                            ]
                            : null,
                    ];
                }
            ),

            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'available_quantity' => number_format(
                $availableQuantity,
                3,
                '.',
                ''
            ),
            'reorder_level' => $this->reorder_level,

            'is_low_stock' => $availableQuantity <= (float) $this->reorder_level,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
