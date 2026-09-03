<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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

            'product_id' => $this->product_id,

            'product' => $this->whenLoaded(
                'product',
                fn () => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                ]
            ),

            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,

            'price' => $this->price,
            'cost' => $this->cost,

            'weight_kg' => $this->weight_kg,
            'dimensions_cm' => $this->dimensions_cm,

            'track_stock' => $this->track_stock,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,

            'option_values' => ProductOptionValueResource::collection(
                $this->whenLoaded('optionValues')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
