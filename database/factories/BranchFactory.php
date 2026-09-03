<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Branch::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'branch_code' => strtoupper(fake()->unique()->lexify('BR???')),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
