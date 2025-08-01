<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
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
     * Định nghĩa mối quan hệ: Một người dùng có nhiều sản phẩm (nếu là Seller).
     */
    public function products(): HasMany // Kiểu trả về HasMany đã được import
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    /**
     * Định nghĩa mối quan hệ: Một người dùng có nhiều mục trong giỏ hàng.
     */
    public function cart(): HasOne 
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    /**
     * Định nghĩa mối quan hệ: Một người dùng có nhiều đơn hàng.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Định nghĩa mối quan hệ: Một người dùng có nhiều đánh giá.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * Kiểm tra xem người dùng có vai trò 'seller' không.
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller' || $this->role === 'admin';
    }

    /**
     * Kiểm tra xem người dùng có vai trò 'admin' không.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
