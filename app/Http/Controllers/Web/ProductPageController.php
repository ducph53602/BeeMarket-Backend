<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductPageController extends Controller
{
    /**
     * Display the product listing page.
     * Data fetching is primarily done via Alpine.js on the client side for dynamic filtering/pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Lấy các tham số lọc/phân trang từ request web
        $params = $request->only(['q', 'category_id', 'min_price', 'max_price', 'sort_by', 'sort_order', 'page']);

        // Gọi API backend để lấy dữ liệu sản phẩm
        // Đảm bảo URL này khớp với API endpoint của bạn
        // Sử dụng helper url() để tạo URL tuyệt đối cho API nếu cần
        $apiUrl = url('/api/products') . '?' . http_build_query($params);
        $response = Http::get($apiUrl);

        $products = [];
        if ($response->successful()) {
            $products = $response->json();
        } else {
            // Xử lý lỗi nếu API không thành công
            // Ví dụ: log lỗi, hoặc trả về một thông báo lỗi cho người dùng
            // dd($response->body());
        }

        // Lấy danh sách categories cho bộ lọc
        $categoriesResponse = Http::get(url('/api/categories'));
        $categories = [];
        if ($categoriesResponse->successful()) {
            $categories = $categoriesResponse->json();
        }

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->all(['q', 'category_id', 'min_price', 'max_price', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified product on the frontend.
     *
     * @param  string  $slug
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function show($slug)
    {
        $product = Product::with('category', 'reviews.user')->where('slug', $slug)->firstOrFail();
        return Inertia::render('Product/Show', [
        'product' => $product,
    ]);

        // Nếu bạn cần gọi API để lấy sản phẩm, có thể làm như sau:
        // $apiUrl = url('/api/products/' . $slug);
        // $response = Http::get($apiUrl);
        // if ($response->successful()) {
        //     $product = $response->json();
        // } else {
        //     // Xử lý lỗi nếu API không thành công
        //     return redirect()->route('products.index')->withErrors(['Product not found']);
        // }

        // return Inertia::render('Product/Show', [
        //     'product' => $product,
        // ]);
    }
}