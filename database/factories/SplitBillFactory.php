<?php

namespace Database\Factories;

use App\Models\SplitBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SplitBillFactory extends Factory
{
    protected $model = SplitBill::class;

    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'total_amount' => fake()->numberBetween(50000, 500000),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => 'active',
        ];
    }
}
