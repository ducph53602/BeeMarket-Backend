<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (Không cần xác thực)
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('api.products.show'); // Use slug for public show
Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/banners', [BannerController::class, 'index'])->name('api.banners.index');
Route::get('/products/{product:slug}/reviews', [ReviewController::class, 'productReviews'])->name('api.product.reviews');

// Authentication routes (Laravel Breeze API)
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

// Authenticated routes (Yêu cầu đăng nhập)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/update/{cartItem}', [CartController::class, 'update']);
        Route::delete('/remove/{cartItem}', [CartController::class, 'remove']);
        Route::post('/checkout', [CartController::class, 'checkout']);
    });

    // Order API (User specific)
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Review API (User specific)
    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']); // Create review for a product
    Route::put('/reviews/{review}', [ReviewController::class, 'update']); // Update own review
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Seller Routes (Requires 'seller' or 'admin' role)
    Route::middleware('role:seller')->prefix('seller')->group(function () {
        Route::get('/products', [ProductController::class, 'sellerProducts']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{productId}', [ProductController::class, 'update']);
        Route::delete('/products/{productId}', [ProductController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'sellerOrders']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder']);

        Route::get('/reviews', [ReviewController::class, 'sellerReviews']);
    });

    Route::middleware('role:seller,admin')->prefix('seller-admin-products')->group(function () {
        Route::get('/', [ProductController::class, 'listProductsForSellerAdmin']); // Hoặc đổi tên thành listProductsForSellerAdmin
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{productId}', [ProductController::class, 'update']);
        Route::delete('/{productId}', [ProductController::class, 'destroy']);
    });
    // Admin Routes (Requires 'admin' role)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Product Management
        Route::get('/products', [ProductController::class, 'adminProducts']);
        Route::delete('/products/{productId}', [ProductController::class, 'destroyByAdmin']);
        Route::put('/products/{productId}', [ProductController::class, 'updateByAdmin']); // Nếu admin có logic cập nhật sản phẩm khác seller

        // Category Management
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::apiResource('/categories', CategoryController::class)->except(['index', 'show']); // Index/show are public

        // Banner Management (UPDATED)
        Route::get('/banners', [BannerController::class, 'adminIndex']); // Explicitly add GET for admin list
        Route::post('/banners', [BannerController::class, 'store']); // Create new banner
        Route::get('/banners/{banner}', [BannerController::class, 'show']); // Get single banner for admin
        Route::put('/banners/{banner}', [BannerController::class, 'update']); // Update banner
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy']);

        // Order Management
        Route::get('/orders', [OrderController::class, 'adminOrders']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroyByAdmin']); // <-- THÊM DÒNG NÀY NẾU BẠN MUỐN XÓA ĐƠN HÀNG BẰNG ADMIN
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder']); // Admin can use seller's cancel route

        // Review Management
        Route::get('/reviews', [ReviewController::class, 'adminReviews']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroyByAdmin']);

        // User Role Management
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    });
});
