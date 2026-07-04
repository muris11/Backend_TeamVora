<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecurringBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringBill>
 */
class RecurringBillFactory extends Factory
{
    protected $model = RecurringBill::class;

    public function definition(): array
    {
        $frequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom_days'];

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'creator_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 10_000, 500_000),
            'frequency' => fake()->randomElement($frequencies),
            'interval_days' => null,
            'due_day' => fake()->optional()->numberBetween(1, 28),
            'status' => 'active',
            'start_date' => today()->subMonth(),
            'end_date' => null,
            'last_generated_at' => null,
            'next_generation_at' => today()->addDay(),
            'assignee_ids' => [User::factory()->create()->id],
            'notify_days_before_due' => 3,
        ];
    }
}
