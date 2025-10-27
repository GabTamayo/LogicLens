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
            'activity_id' => 'ba28ef80-a0a8-4196-b54a-847e5e249837',
            'token' => fake()->unique()->uuid(),
            'name' => fake()->word(),
            'is_open' => true,
            'expires_at' => null,
        ];
    }
}
