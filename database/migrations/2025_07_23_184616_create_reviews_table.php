<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Người dùng đánh giá
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Sản phẩm được đánh giá
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null'); // Mục đơn hàng cụ thể đã mua (để xác minh)
            $table->tinyInteger('rating')->unsigned(); // Số sao đánh giá (ví dụ: 1-5)
            $table->text('comment')->nullable(); // Bình luận
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};