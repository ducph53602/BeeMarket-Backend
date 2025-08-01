<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id, // Link to an existing customer user
            'product_id' => Product::inRandomOrder()->first()->id, // Link to an existing product
            'rating' => fake()->numberBetween(1, 5), // Rating from 1 to 5 stars
            'comment' => fake()->paragraph(rand(1, 3)),
        ];
    }
}

