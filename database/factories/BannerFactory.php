<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banner>
 */
class BannerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(rand(3, 6)), // A short sentence for the banner title
            'image_url' => fake()->imageUrl(1200, 400, 'advertisement', true, 'Faker', true), // Placeholder image URL for banners
            'target_url' => fake()->url(), // A random URL for the banner to link to
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }
}
