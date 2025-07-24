<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\User
 *
 * @method bool isAdmin()
 * @method bool isSeller()
 * @method bool isUser()
 * @property string $role
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cart for the user.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller' || $this->role === 'admin'; 
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get the products for the user (if they are a seller).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id'); 
    }

    /**
     * Một người dùng có thể có nhiều mục đơn hàng (qua các đơn hàng của họ).
     */
    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }
    
    /**
     * Một người dùng có thể viết nhiều đánh giá.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

