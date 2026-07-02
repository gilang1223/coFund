<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory()->creator(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6),
            'description' => fake()->paragraphs(3, true),
            'target_amount' => fake()->randomFloat(2, 1000000, 50000000),
            'collected_amount' => 0,
            'deadline' => now()->addDays(30),
            'status' => 'active',
            'video_url' => null,
        ];
    }

    /**
     * Indicate that the campaign has reached its target.
     */
    public function reachedTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'collected_amount' => $attributes['target_amount'],
        ]);
    }

    /**
     * Indicate that the campaign is expired (deadline in the past).
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the campaign is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the campaign is in review.
     */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'review',
        ]);
    }

    /**
     * Indicate that the campaign is successful.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
        ]);
    }

    /**
     * Indicate that the campaign has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
