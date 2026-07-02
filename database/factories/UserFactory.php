<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'backer',
            'balance' => fake()->randomFloat(2, 100000, 5000000),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin CoFund',
            'email' => 'admin@cofund.com',
            'role' => 'admin',
            'balance' => 0,
        ]);
    }

    /**
     * Indicate that the user is a creator.
     */
    public function creator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'creator',
            'balance' => fake()->randomFloat(2, 500000, 10000000),
        ]);
    }

    /**
     * Indicate that the user is a backer.
     */
    public function backer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'backer',
            'balance' => fake()->randomFloat(2, 200000, 8000000),
        ]);
    }
}
