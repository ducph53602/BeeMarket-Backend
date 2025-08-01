<?php 

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::inRandomOrder()->first()->id, // Link to an existing cart
            'product_id' => Product::inRandomOrder()->first()->id, // Link to an existing product
            'quantity' => fake()->numberBetween(1, 5), // Quantity between 1 and 5
        ];
    }
}