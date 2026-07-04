<?php

namespace Database\Factories;

use App\Models\TeamMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMediaFactory extends Factory
{
    protected $model = TeamMedia::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['document', 'gallery']),
            'name' => fake()->word() . '.' . fake()->fileExtension(),
            'file_path' => fake()->url(),
            'size' => fake()->numberBetween(1000, 5000000),
            'mime_type' => fake()->mimeType(),
        ];
    }
}
