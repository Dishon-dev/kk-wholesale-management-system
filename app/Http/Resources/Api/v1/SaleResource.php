<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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

            'store' => $this->whenLoaded('store', function () {
                return [
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'store_code' => $this->store->store_code,
                ];
            }),

            'customer' => $this->whenLoaded('customer', function () {
                return $this->customer ? [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ] : null;
            }),

            'created_by' => $this->whenLoaded('createdBy', function () {
                return $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ] : null;
            }),

            'sale_date' => $this->sale_date?->toISOString(),

            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total' => $this->total,

            'paid_amount' => $this->paid_amount,
            'balance_due' => $this->balance_due,
            'change_amount' => $this->change_amount,

            'payment_status' => $this->payment_status,
            'status' => $this->status,

            'notes' => $this->notes,

            'items' => SaleItemResource::collection(
                $this->whenLoaded('items')
            ),

            'payments' => SalePaymentResource::collection(
                $this->whenLoaded('payments')
            ),

            'returns' => SaleReturnResource::collection(
                $this->whenLoaded('returns')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
