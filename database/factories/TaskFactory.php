<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['high', 'medium', 'low']),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'finish_date' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
