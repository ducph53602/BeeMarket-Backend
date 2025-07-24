<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Tên của model tương ứng.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Định nghĩa trạng thái mặc định của model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), 
            'remember_token' => Str::random(10),
            'phone_number' => $this->faker->phoneNumber(), 
            'address' => $this->faker->address(),         
            'is_seller' => $this->faker->boolean(50),  
            'is_admin' => false,   
        ];
    }

    /**
     * Indicate that the user is unverified.
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a seller.
     */
    public function seller(): static 
    {
        return $this->state(fn (array $attributes) => [
            'is_seller' => true,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static 
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }
}