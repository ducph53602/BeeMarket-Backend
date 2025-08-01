<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Review;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. User Seeder Logic
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Seller Users
        User::factory()->create([
            'name' => 'Seller One',
            'email' => 'seller1@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        User::factory()->create([
            'name' => 'Seller Two',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        // Regular Users (Customers)
        User::factory()->create([
            'name' => 'Customer One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'Customer Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create 5 more random users
        User::factory(5)->create();

        // Get all users for later use
        $allUsers = User::all();
        $customers = $allUsers->where('role', 'user');
        $sellers = $allUsers->whereIn('role', ['seller', 'admin']);


        // 2. Category Seeder Logic
        $categoriesData = [
            'Điện tử',
            'Thời trang',
            'Đồ gia dụng',
            'Sách & Văn phòng phẩm',
            'Đồ chơi & Trẻ em',
            'Thể thao & Du lịch',
            'Nội thất & Trang trí',
            'Phụ kiện',
        ];

        foreach ($categoriesData as $categoryName) {
            Category::factory()->create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
            ]);
        }
        $categories = Category::all();


        // 3. Product Seeder Logic
        if ($sellers->isEmpty() || $categories->isEmpty()) {
            echo "Skipping Product seeding: No sellers or categories found.\n";
        } else {
            $productsToSeed = [
                [
                    'name' => 'Điện thoại cũ iPhone X',
                    'description' => 'iPhone X 64GB, màu bạc, tình trạng 8/10, pin 85%. Hoạt động tốt, không lỗi lầm. Phù hợp cho người dùng cơ bản hoặc làm máy phụ.',
                    'price' => 5000000,
                    'quantity' => 5,
                    'image' => '/storage/products/iphone-x.jpg',
                    'category_name' => 'Điện tử',
                ],
                [
                    'name' => 'Laptop Dell Latitude E7470',
                    'description' => 'Laptop văn phòng, cấu hình i5-6300U, RAM 8GB, SSD 256GB. Màn hình 14 inch Full HD. Máy chạy mượt, phù hợp học tập, làm việc.',
                    'price' => 7500000,
                    'quantity' => 3,
                    'image' => '/storage/products/dell-laptop.jpg',
                    'category_name' => 'Điện tử',
                ],
                [
                    'name' => 'Áo khoác da nam',
                    'description' => 'Áo khoác da thật, size L, màu đen. Tình trạng 9/10, ít sử dụng. Phong cách bụi bặm, cá tính.',
                    'price' => 800000,
                    'quantity' => 2,
                    'image' => '/storage/products/leather-jacket.jpg',
                    'category_name' => 'Thời trang',
                ],
                [
                    'name' => 'Bàn làm việc gỗ sồi',
                    'description' => 'Bàn làm việc gỗ sồi tự nhiên, kích thước 120x60x75cm. Chắc chắn, bền đẹp, phù hợp không gian làm việc hiện đại.',
                    'price' => 1200000,
                    'quantity' => 1,
                    'image' => '/storage/products/oak-desk.jpg',
                    'category_name' => 'Nội thất & Trang trí',
                ],
                [
                    'name' => 'Tủ lạnh mini Electrolux',
                    'description' => 'Tủ lạnh mini dung tích 50 lít, phù hợp phòng trọ hoặc cá nhân. Hoạt động êm ái, tiết kiệm điện.',
                    'price' => 1500000,
                    'quantity' => 1,
                    'image' => '/storage/products/mini-fridge.jpg',
                    'category_name' => 'Đồ gia dụng',
                ],
                [
                    'name' => 'Giày thể thao Nike Air Max',
                    'description' => 'Giày Nike Air Max chính hãng, size 42, màu trắng. Còn mới 90%, đế không mòn. Thích hợp đi chơi, tập luyện.',
                    'price' => 1800000,
                    'quantity' => 1,
                    'image' => '/storage/products/nike-air-max.jpg',
                    'category_name' => 'Thời trang',
                ],
                [
                    'name' => 'Sách "Đắc nhân tâm"',
                    'description' => 'Bản tiếng Việt, tình trạng 9/10. Sách kinh điển về kỹ năng giao tiếp và đối nhân xử thế.',
                    'price' => 85000,
                    'quantity' => 10,
                    'image' => '/storage/products/dac-nhan-tam.jpg',
                    'category_name' => 'Sách & Văn phòng phẩm',
                ],
                [
                    'name' => 'Xe đạp địa hình cũ',
                    'description' => 'Xe đạp địa hình khung nhôm, bánh 26 inch. Phù hợp đi lại hàng ngày hoặc tập thể dục nhẹ.',
                    'price' => 2500000,
                    'quantity' => 1,
                    'image' => '/storage/products/mountain-bike.jpg',
                    'category_name' => 'Thể thao & Du lịch',
                ],
                [
                    'name' => 'Balo du lịch 50L',
                    'description' => 'Balo chuyên dụng cho các chuyến đi dài ngày, chống nước tốt, nhiều ngăn tiện lợi.',
                    'price' => 450000,
                    'quantity' => 3,
                    'image' => '/storage/products/travel-backpack.jpg',
                    'category_name' => 'Thể thao & Du lịch',
                ],
                [
                    'name' => 'Đèn bàn học LED',
                    'description' => 'Đèn bàn học chống cận, ánh sáng vàng dịu, có thể điều chỉnh độ sáng. Tiết kiệm điện.',
                    'price' => 200000,
                    'quantity' => 7,
                    'image' => '/storage/products/desk-lamp.jpg',
                    'category_name' => 'Đồ gia dụng',
                ],
            ];

            foreach ($productsToSeed as $data) {
                $category = $categories->firstWhere('name', $data['category_name']);
                if ($category) {
                    Product::factory()->create([
                        'name' => $data['name'],
                        'slug' => Str::slug($data['name']),
                        'description' => $data['description'],
                        'price' => $data['price'],
                        'quantity' => $data['quantity'],
                        'image' => $data['image'],
                        'user_id' => $sellers->random()->id, // Assign to a random seller
                        'category_id' => $category->id,
                    ]);
                }
            }

            // Create 20 more random products
            Product::factory(20)->create([
                'user_id' => $sellers->random()->id,
                'category_id' => $categories->random()->id,
            ]);
        }
        $products = Product::all();


        // 4. Banner Seeder Logic
        Banner::factory()->create([
            'title' => 'Giảm giá 50% cho đồ điện tử!',
            'image_url' => '/storage/banners/banner-electronics.jpg',
            'target_url' => '/products?category_id=1', // Assuming category ID 1 is electronics
            'is_active' => true,
        ]);

        Banner::factory()->create([
            'title' => 'Miễn phí vận chuyển toàn quốc!',
            'image_url' => '/storage/banners/banner-freeship.jpg',
            'target_url' => '/products',
            'is_active' => true,
        ]);

        Banner::factory()->create([
            'title' => 'Đồ gia dụng cũ như mới!',
            'image_url' => '/storage/banners/banner-home-appliances.jpg',
            'target_url' => '/products?category_id=3', // Assuming category ID 3 is home appliances
            'is_active' => true,
        ]);

        // 5. Cart Seeder Logic
        if ($customers->isEmpty()) {
            echo "Skipping Cart seeding: No customers found.\n";
        } else {
            // Create a cart for each customer
            foreach ($customers as $user) {
                Cart::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        }
        $carts = Cart::all();


        // 6. Order Seeder Logic
        if ($customers->isEmpty()) {
            echo "Skipping Order seeding: No customers found.\n";
        } else {
            // Create 5 random orders for different users
            Order::factory(5)->create([
                'user_id' => $customers->random()->id,
                'total_amount' => rand(100000, 5000000), // Random total amount
                'status' => ['pending', 'completed', 'cancelled'][array_rand(['pending', 'completed', 'cancelled'])],
            ]);

            // Create a specific order for Customer One
            $customerOne = $customers->firstWhere('email', 'user1@example.com');
            if ($customerOne) {
                Order::factory()->create([
                    'user_id' => $customerOne->id,
                    'total_amount' => 1250000,
                    'status' => 'completed',
                ]);
            }
        }
        $orders = Order::all();


        // 7. Review Seeder Logic
        if ($customers->isEmpty() || $products->isEmpty()) {
            echo "Skipping Review seeding: No customers or products found.\n";
        } else {
            // Create some specific reviews
            $iphoneX = $products->firstWhere('name', 'Điện thoại cũ iPhone X');
            $leatherJacket = $products->firstWhere('name', 'Áo khoác da nam');

            if ($iphoneX && $customers->first()) {
                Review::factory()->create([
                    'user_id' => $customers->first()->id, // Customer One
                    'product_id' => $iphoneX->id,
                    'rating' => 5,
                    'comment' => 'Sản phẩm rất tốt, giao hàng nhanh chóng và đóng gói cẩn thận!',
                ]);
            }

            if ($leatherJacket && $customers->last()) {
                Review::factory()->create([
                    'user_id' => $customers->last()->id, // Customer Two
                    'product_id' => $leatherJacket->id,
                    'rating' => 4,
                    'comment' => 'Áo đẹp, da xịn, nhưng hơi rộng một chút so với mô tả.',
                ]);
            }

            // Create 10 more random reviews
            Review::factory(10)->create([
                'user_id' => $customers->random()->id,
                'product_id' => $products->random()->id,
            ]);
        }


        // 8. Cart Item Seeder Logic
        if ($carts->isEmpty() || $products->isEmpty()) {
            echo "Skipping CartItem seeding: No carts or products found.\n";
        } else {
            // Add some random items to each cart
            foreach ($carts as $cart) {
                // Add 1 to 3 unique products to each cart
                $numItems = rand(1, 3);
                $selectedProducts = $products->random($numItems);

                foreach ($selectedProducts as $product) {
                    // Check if the product is already in the cart to avoid unique constraint violation
                    $existingCartItem = CartItem::where('cart_id', $cart->id)
                                                ->where('product_id', $product->id)
                                                ->first();
                    if (!$existingCartItem) {
                        CartItem::factory()->create([
                            'cart_id' => $cart->id,
                            'product_id' => $product->id,
                            'quantity' => rand(1, 2), // 1 or 2 items of each product
                        ]);
                    }
                }
            }
        }


        // 9. Order Item Seeder Logic
        if ($orders->isEmpty() || $products->isEmpty()) {
            echo "Skipping OrderItem seeding: No orders or products found.\n";
        } else {
            foreach ($orders as $order) {
                // Add 1 to 3 unique products to each order
                $numItems = rand(1, 3);
                $selectedProducts = $products->random($numItems);

                foreach ($selectedProducts as $product) {
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 2),
                        'price' => $product->price, // Use product's current price
                    ]);
                }
            }
        }
    }
}
