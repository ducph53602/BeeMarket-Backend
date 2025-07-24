<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $products = [];
        $banners = [];
        $errorMessage = null;

        try {
            // Gọi API sản phẩm của bạn
            $productsResponse = Http::get(config('app.url') . '/api/products');
            if ($productsResponse->successful()) {
                $products = $productsResponse->json()['products']['data'] ?? []; // Điều chỉnh nếu cấu trúc response khác
            } else {
                $errorMessage = 'Không thể tải sản phẩm.';
                Log::error('API Products Error: ' . $productsResponse->body());
            }

            // Gọi API banner của bạn
            $bannersResponse = Http::get(config('app.url') . '/api/banners');
            if ($bannersResponse->successful()) {
                $banners = $bannersResponse->json()['banners'] ?? []; // Điều chỉnh nếu cấu trúc response khác
            } else {
                $errorMessage = 'Không thể tải banner.';
                Log::error('API Banners Error: ' . $bannersResponse->body());
            }

        } catch (\Exception $e) {
            $errorMessage = 'Đã xảy ra lỗi khi kết nối dữ liệu: ' . $e->getMessage();
            Log::error('HomeController Error: ' . $e->getMessage());
        }

        return view('home', [
            'products' => $products,
            'banners' => $banners,
            'error' => $errorMessage
        ]);
    }
}