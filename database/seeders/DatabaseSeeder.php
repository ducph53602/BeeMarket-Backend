<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product; 
use App\Models\Banner;
use App\Models\Cart;
use App\Models\CartItem;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), 
            'is_admin' => true,
            'is_seller' => true,
        ]);

        User::factory()->create([
            'name' => 'Seller User',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'), 
            'is_admin' => false,
            'is_seller' => true,
        ]);

        User::factory()->create([
            'name' => 'Tester User',
            'email' => 'tester@example.com',
            'password' => bcrypt('password'), 
            'is_admin' => false,
            'is_seller' => false,
        ]);

        User::factory()->create([
            'name' => 'Tester2 User',
            'email' => 'tester2@example.com',
            'password' => bcrypt('password'), 
            'is_admin' => false,
            'is_seller' => false,
        ]);

        User::factory(30)->create();

        $users = User::all();

        if ($users->isEmpty()) {
            throw new \Exception("Không tìm thấy người dùng nào để gán cho sản phẩm. Kiểm tra lại.");
        }
        
        Product::factory(30)->make()->each(function ($product) use ($users) {
            $product->user_id = $users->random()->id; 
            $product->save();
        });


        Banner::factory()->create([
            'title' => 'Ưu đãi Lớn!',
            'subtitle' => 'Giảm giá 50% tất cả sản phẩm',
            'image_path' => 'banners/banner1.jpg',
            'link' => '/products?sale=true',
            'is_active' => true,
            'order' => 1,
        ]);
        Banner::factory()->create([
            'title' => 'Sản phẩm mới về',
            'subtitle' => 'Khám phá ngay bộ sưu tập mới!',
            'image_path' => 'banners/banner2.jpg', 
            'link' => '/products?new=true',
            'is_active' => true,
            'order' => 2,
        ]);

        Cart::factory(5)->create()->each(function ($cart) {
            $products = Product::inRandomOrder()->limit(rand(1, 5))->get();
            foreach ($products as $product) {
                CartItem::factory()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        });
    }
}
