<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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

            'branch' => $this->whenLoaded(
                'branch',
                fn () => [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                    'branch_code' => $this->branch->branch_code,
                ]
            ),

            'branch_id' => $this->branch_id,

            'name' => $this->name,
            'store_code' => $this->store_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,

            'users_count' => $this->whenCounted('users'),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
