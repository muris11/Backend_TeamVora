<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'assignee_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => 'todo',
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}
