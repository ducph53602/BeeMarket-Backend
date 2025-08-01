<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'user')->inRandomOrder()->first()->id, // Link to an existing customer user
            'total_amount' => fake()->randomFloat(2, 100000, 10000000), // Total amount between 100,000 and 10,000,000 VND
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'shipping_address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
        ];
    }
}

