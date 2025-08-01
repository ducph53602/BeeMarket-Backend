<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Create a unique product name
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(rand(3, 7)),
            'price' => $this->faker->randomFloat(2, 50000, 5000000), // Prices between 50k and 5M VND
            'quantity' => $this->faker->numberBetween(0, 100), // Quantity between 0 and 50
            'image' => 'products/' . fake()->image('public/storage/products', 640, 480, null, false) . '.jpg', // Generates a dummy image file and returns its path
            'user_id' => User::whereIn('role', ['seller', 'admin'])->inRandomOrder()->first()->id,
            'category_id' => Category::inRandomOrder()->first()->id,  
        ];
    }
}

