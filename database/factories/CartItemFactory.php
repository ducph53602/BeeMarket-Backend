<?php

namespace Database\Factories;

use App\Models\CartItem; 
use App\Models\Cart;     
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cartId = Cart::inRandomOrder()->first()->id ?? Cart::factory()->create()->id;
        $productId = Product::inRandomOrder()->first()->id ?? Product::factory()->create()->id;
        if (!$cartId || !$productId) {
            throw new \Exception("Không tìm thấy giỏ hàng hoặc sản phẩm để tạo CartItem. Kiểm tra lại.");
        }
        return [
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
