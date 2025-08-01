<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    /**
     * Lấy danh sách tất cả sản phẩm (cho Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $products = Product::with('user', 'category')
                           ->orderBy('created_at', 'desc')
                           ->paginate($request->input('per_page', 10));

        return response()->json($products);
    }

    /**
     * Xóa một sản phẩm (cho Admin).
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        // Xóa ảnh liên quan nếu có
        if ($product->image_url) {
            Storage::delete(str_replace('/storage/', 'public/', $product->image_url));
        }

        $product->delete();

        return response()->json(['message' => 'Sản phẩm đã được xóa bởi Admin.'], 200);
    }
}
