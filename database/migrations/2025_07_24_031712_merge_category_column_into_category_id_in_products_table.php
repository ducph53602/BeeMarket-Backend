<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade
use App\Models\Category; // Import Category Model
use App\Models\Product; // Import Product Model
use Illuminate\Support\Str; // Import Str facade for slug

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'category')) {
            $products = DB::table('products')->whereNotNull('category')->get();

            foreach ($products as $product) {
                $categoryName = $product->category;
                $categorySlug = Str::slug($categoryName);

                $category = Category::firstOrCreate(
                    ['slug' => $categorySlug], // Điều kiện tìm kiếm
                    ['name' => $categoryName, 'description' => null] // Dữ liệu để tạo nếu không tìm thấy
                );

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['category_id' => $category->id]);
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('category_id');
        });
    }
};