<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;  

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_path',
        'category_id',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * Get the user (seller) that owns the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Một sản phẩm thuộc về một danh mục.
     */
    public function category() // Định nghĩa mối quan hệ
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /**
     * Một sản phẩm có nhiều đánh giá.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Tính toán điểm đánh giá trung bình.
     */
    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    /**
     * Đếm tổng số đánh giá.
     */
    public function reviewsCount()
    {
        return $this->reviews()->count();
    }
}