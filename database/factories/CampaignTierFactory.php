<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignTier>
 */
class CampaignTierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CampaignTier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'name' => fake()->words(2, true),
            'min_amount' => fake()->randomFloat(2, 50000, 500000),
            'quota' => fake()->numberBetween(10, 100),
            'remaining_quota' => fn (array $attrs) => $attrs['quota'],
            'reward_description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the tier is sold out.
     */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'remaining_quota' => 0,
        ]);
    }
}
