<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnResource extends JsonResource
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
            'reference' => $this->reference,

            'sale' => $this->whenLoaded('sale', function () {
                return [
                    'id' => $this->sale->id,
                    'reference' => $this->sale->reference,
                    'total' => $this->sale->total,
                ];
            }),

            'store' => $this->whenLoaded('store', function () {
                return [
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'store_code' => $this->store->store_code,
                ];
            }),

            'created_by' => $this->whenLoaded('createdBy', function () {
                return $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ] : null;
            }),

            'return_date' => $this->return_date?->toISOString(),

            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,

            'status' => $this->status,
            'reason' => $this->reason,

            'items' => SaleReturnItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
