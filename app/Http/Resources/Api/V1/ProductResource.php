<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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

            'category' => $this->whenLoaded(
                'category',
                fn () => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ]
            ),

            'brand' => $this->whenLoaded(
                'brand',
                fn () => [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'slug' => $this->brand->slug,
                ]
            ),

            'name' => $this->name,
            'slug' => $this->slug,

            'short_description' => $this->short_description,
            'description' => $this->description,

            'currency' => $this->currency,

            'weight_kg' => $this->weight_kg,
            'dimensions_cm' => $this->dimensions_cm,
            'gallery' => $this->gallery,

            'is_featured' => $this->is_featured,
            'is_bestseller' => $this->is_bestseller,
            'is_active' => $this->is_active,

            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            'options' => ProductOptionResource::collection(
                $this->whenLoaded('options')
            ),

            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),

            'default_variant' => new ProductVariantResource(
                $this->whenLoaded('defaultVariant')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
