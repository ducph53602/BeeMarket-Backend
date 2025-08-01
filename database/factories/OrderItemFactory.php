<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();

        return [
            'order_id' => Order::inRandomOrder()->first()->id, // Link to an existing order
            'product_id' => $product->id, // Link to the selected product
            'quantity' => fake()->numberBetween(1, 3), // Quantity between 1 and 3
            'price' => $product->price, // Use the actual price of the product at the time of order
        ];
    }
}

