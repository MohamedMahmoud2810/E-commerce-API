<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(), // Assumes ProductFactory exists
            'user_id' => User::factory(), // Assumes UserFactory exists
            'review' => $this->faker->text(200),
            'rating' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'is_spam' => $this->faker->boolean,
        ];
    }
}
