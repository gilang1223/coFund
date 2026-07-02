<?php

namespace Database\Factories;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Backing>
 */
class BackingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Backing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->backer(),
            'campaign_id' => Campaign::factory(),
            'tier_id' => null,
            'amount' => fake()->randomFloat(2, 50000, 5000000),
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the backing is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the backing is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}
