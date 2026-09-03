<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product_variant' => $this->whenLoaded(
                'productVariant',
                function () {
                    return [
                        'id' => $this->productVariant->id,
                        'name' => $this->productVariant->name,
                        'sku' => $this->productVariant->sku,
                        'barcode' => $this->productVariant->barcode,

                        'product' => $this->productVariant->relationLoaded('product')
                            ? [
                                'id' => $this->productVariant->product->id,
                                'name' => $this->productVariant->product->name,
                            ]
                            : null,
                    ];
                }
            ),

            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'reason' => $this->reason,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
