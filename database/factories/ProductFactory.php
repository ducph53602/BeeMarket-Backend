<?php

namespace Database\Factories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $productName = $this->faker->words(3, true) . ' ' . $this->faker->unique()->word(); 
        $statuses = ['active', 'inactive'];

        $category = Category::inRandomOrder()->first();

        $user = User::inRandomOrder()->first();

        return [
            'name' => ucfirst($productName),
            'slug' => Str::slug($productName) . '-' . $this->faker->unique()->randomNumber(4), 
            'description' => $this->faker->paragraph(2),
            'price' => $this->faker->randomFloat(2, 10, 1000), 
            'stock' => $this->faker->numberBetween(0, 200), 
            'image_path' => 'products/' . $this->faker->uuid() . '.jpg', 
            'category_id' => $category->id,
            'status' => $this->faker->randomElement($statuses),
            'user_id' => $user->id,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}