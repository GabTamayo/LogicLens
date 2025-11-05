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
            'activity_link_id' => '6c55f805-edc5-4a1a-842a-3adc3faa693a',
            'student_name' => fake()->name(),
            'student_email' => fake()->unique()->safeEmail(),
            'student_no' => fake()->unique()->numerify('S########'),
            'file_path' => fake()->filePath(),
        ];
    }
}
