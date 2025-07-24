<?php
namespace App\Http\Controllers\Web; 

use App\Models\Product;
use App\Http\Controllers\Controller;
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
            $baseUrl = config('app.url'); // Đảm bảo APP_URL trong .env đã được cấu hình

            Log::info("Attempting to fetch products from: {$baseUrl}/api/products");
            $productsResponse = Http::get("{$baseUrl}/api/products");
            if ($productsResponse->successful()) {
                $products = $productsResponse->json()['products']['data'] ?? [];
            } else {
                $errorMessage = 'Không thể tải sản phẩm. Mã lỗi: ' . $productsResponse->status();
                Log::error('API Products Error in HomeController: ' . $productsResponse->status() . ' - ' . $productsResponse->body());
            }

            Log::info("Attempting to fetch banners from: {$baseUrl}/api/banners");
            $bannersResponse = Http::get("{$baseUrl}/api/banners");
            if ($bannersResponse->successful()) {
                $banners = $bannersResponse->json()['banners'] ?? [];
            } else {
                $errorMessage = 'Không thể tải banner. Mã lỗi: ' . $bannersResponse->status();
                Log::error('API Banners Error in HomeController: ' . $bannersResponse->status() . ' - ' . $bannersResponse->body());
            }

        } catch (\Exception $e) {
            $errorMessage = 'Đã xảy ra lỗi khi kết nối dữ liệu: ' . $e->getMessage();
            Log::error('HomeController Exception: ' . $e->getMessage(), ['exception' => $e]);
        }

        return view('home', compact('products', 'banners', 'errorMessage'));
    }
}