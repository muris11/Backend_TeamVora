<?php

namespace Database\Factories;

use App\Models\CashBook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashBookFactory extends Factory
{
    protected $model = CashBook::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['in', 'out']),
            'amount' => fake()->numberBetween(10000, 500000),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'attachment_path' => null,
        ];
    }
}
