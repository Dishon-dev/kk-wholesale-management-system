<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Store::class;
    
    public function definition(): array
    {
        return [

            'branch_id' => Branch::factory(),
            'name' => fake()->company(),
            'store_code' => strtoupper(fake()->unique()->lexify('ST???')),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
