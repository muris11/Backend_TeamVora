<?php

namespace Database\Factories;

use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyLogFactory extends Factory
{
    protected $model = DailyLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'log_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'content' => fake()->paragraph(),
            'attachment_path' => null,
        ];
    }
}
