<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    /**
     * Lấy danh sách tất cả danh mục (cho Admin).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name', 'asc')->get();
        return response()->json($categories);
    }

    /**
     * Thêm danh mục mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Danh mục đã được thêm thành công.', 'category' => $category], 201);
    }

    /**
     * Cập nhật thông tin danh mục.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Danh mục đã được cập nhật thành công.', 'category' => $category], 200);
    }

    /**
     * Xóa một danh mục.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
        if ($category->products()->count() > 0) {
            return response()->json(['message' => 'Không thể xóa danh mục này vì có sản phẩm đang thuộc danh mục này. Vui lòng di chuyển hoặc xóa các sản phẩm trước.'], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Danh mục đã được xóa thành công.'], 200);
    }
}
