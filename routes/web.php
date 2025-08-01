<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CartPageController;
use App\Http\Controllers\Web\OrderPageController;
use App\Http\Controllers\Web\ProductPageController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\SellerDashboardController;
use Inertia\Inertia; // Import Inertia

// Public Routes
Route::get('/', function () {
    return Inertia::render('HomePage'); // Sử dụng Inertia::render
})->name('home');
// Hoặc nếu bạn muốn dùng Controller:
// Route::get('/', [HomeController::class, 'index'])->name('home');
// (Và trong HomeController::index bạn sẽ return Inertia::render('HomePage');)


Route::get('/products', [ProductPageController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductPageController::class, 'show'])->name('products.show');

// Authenticated Routes (Requires user to be logged in)
Route::middleware('auth')->group(function () {

    Route::middleware(['role:seller,admin'])->group(function () {
        Route::get('/seller/dashboard', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart Routes
    Route::get('/cart', [CartPageController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartPageController::class, 'add'])->name('cart.add'); // Sẽ cần xem xét lại, API đã có /api/cart/add
    Route::put('/cart/update/{cartItem}', [CartPageController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{cartItem}', [CartPageController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/checkout', [CartPageController::class, 'checkout'])->name('cart.checkout');

    // Order Routes
    Route::get('/orders', [OrderPageController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderPageController::class, 'show'])->name('orders.show');
});

// Laravel Breeze authentication routes
require __DIR__ . '/auth.php';