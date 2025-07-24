<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\CategoryController; 

// Public API routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Product API routes
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

Route::get('/banners', [BannerController::class, 'publicIndex']);

// Chatbot API routes
Route::prefix('chatbot')->group(function () {
    Route::post('ask', [ChatbotController::class, 'ask']);
});

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('admin')->middleware('can:isAdmin')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('banners', BannerController::class);
        Route::get('products', [ProductController::class, 'adminIndex']); 
        Route::apiResource('products', ProductController::class)->except(['index']);

        Route::get('users', [UserController::class, 'index']); 
        Route::get('users/{user}', [UserController::class, 'show']); 
        Route::put('users/{user}/role', [UserController::class, 'updateRole']);
    });

    // Cart API routes
     Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'viewCart']); 
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::put('/{cartItem}', [CartController::class, 'update']);
        Route::delete('/{cartItem}', [CartController::class, 'destroy']);
        Route::post('/clear', [CartController::class, 'clear']);
        Route::post('/merge', [CartController::class, 'mergeCarts']);
     });
    // Order API routes
    Route::post('orders', [OrderController::class, 'placeOrder']);
    Route::get('orders', [OrderController::class, 'index']); 
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('can:isSellerOrAdmin');

    // Seller API routes
    Route::prefix('seller')->middleware('can:isSeller')->group(function () {
        Route::get('products/my-products', [ProductController::class, 'sellerProducts']);
        Route::apiResource('products', ProductController::class)->except(['index']); 
    });

    // Review API routes
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']); 
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});
