<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductController;

Route::group([], function () {
    // Route cho trang chủ
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Các route cho Sản phẩm
    // Hiển thị danh sách tất cả sản phẩm
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // Hiển thị chi tiết một sản phẩm theo slug
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
