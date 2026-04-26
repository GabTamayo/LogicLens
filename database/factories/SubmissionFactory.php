<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_link_id' => '99062b03-6560-4000-b491-37716f86fb10',
            'user_id' => \App\Models\User::factory(),
            'code_content' => fake()->paragraph(),
            'language' => 'java',
        ];
    }
}
