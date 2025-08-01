<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách tất cả danh mục.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::withCount('products')->get(); // Đếm số lượng sản phẩm trong mỗi danh mục
        return response()->json($categories);
    }

    /**
     * Store a newly created category in storage.
     * Admin API: POST /api/admin/categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorization handled by middleware 'role:admin'
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($category, 201);
    }

    /**
     * Update the specified category in storage.
     * Admin API: PUT /api/admin/categories/{category}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        // Authorization handled by middleware 'role:admin'
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($category);
    }

    /**
     * Remove the specified category from storage.
     * Admin API: DELETE /api/admin/categories/{category}
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        // Authorization handled by middleware 'role:admin'
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete category with associated products.'], 409);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.'], 200);
    }
}
