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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'subtitle' => $this->faker->sentence(5),
            'image_path' => 'banners/' . $this->faker->uuid() . '.jpg', // Example image path
            'link' => $this->faker->url(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'order' => $this->faker->unique()->numberBetween(1, 10),
        ];
    }
}
