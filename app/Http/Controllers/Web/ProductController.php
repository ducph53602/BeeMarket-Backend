<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = [];
        $errorMessage = null;
        $searchQuery = $request->query('search'); // Lấy từ khóa tìm kiếm từ URL

        try {
            $baseUrl = config('app.url');
            $queryParams = $searchQuery ? ['search' => $searchQuery] : [];

            $response = Http::get("{$baseUrl}/api/products", $queryParams);

            if ($response->successful()) {
                $products = $response->json()['products'] ?? []; // Có thể là products.data hoặc chỉ products tùy API
                // Nếu API của bạn trả về object phân trang, hãy lấy data từ đó:
                // $products = $response->json()['products']['data'] ?? [];
                // $pagination = $response->json()['products'] ?? []; // Để dùng cho phân trang Blade
            } else {
                $errorMessage = 'Không thể tải danh sách sản phẩm.';
                Log::error('API Products Index Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $errorMessage = 'Đã xảy ra lỗi khi kết nối dữ liệu: ' . $e->getMessage();
            Log::error('ProductController Index Exception: ' . $e->getMessage());
        }

        return view('products.index', compact('products', 'errorMessage', 'searchQuery'));
    }

    public function show($id)
    {
        $product = null;
        $errorMessage = null;

        try {
            $baseUrl = config('app.url');
            $response = Http::get("{$baseUrl}/api/products/{$id}");

            if ($response->successful()) {
                $product = $response->json()['product'] ?? null; // Giả định API trả về { "product": {...} }
            } else {
                $errorMessage = 'Không thể tìm thấy sản phẩm.';
                Log::error("API Product Show Error for ID {$id}: " . $response->body());
            }
        } catch (\Exception $e) {
            $errorMessage = 'Đã xảy ra lỗi khi kết nối dữ liệu: ' . $e->getMessage();
            Log::error("ProductController Show Exception for ID {$id}: " . $e->getMessage());
        }

        if (!$product) {
            abort(404, $errorMessage ?? 'Product not found.');
        }

        return view('products.show', compact('product', 'errorMessage'));
    }
}