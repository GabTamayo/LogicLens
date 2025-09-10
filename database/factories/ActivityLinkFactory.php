<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLink>
 */
class ActivityLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => 1,
            'token' => fake()->unique()->uuid(),
            'name' => fake()->word(),
            'status' => fake()->randomElement(['active', 'closed', 'expired']),
            'expires_at' => null,
        ];
    }
}
