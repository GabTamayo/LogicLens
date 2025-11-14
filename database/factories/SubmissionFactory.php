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
            'activity_link_id' => '3a5f470c-9d94-4240-a5dd-47170fb8a396',
            'student_name' => fake()->name(),
            'student_email' => fake()->unique()->safeEmail(),
            'student_no' => fake()->unique()->numerify('S########'),
            'file_path' => fake()->filePath(),
            'language' => 'java'
        ];
    }
}
