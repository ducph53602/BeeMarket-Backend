<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Lấy người dùng sở hữu mục giỏ hàng này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the cart items for the cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}
