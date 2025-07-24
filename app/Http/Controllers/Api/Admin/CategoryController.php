<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách tất cả các danh mục.
     * GET /api/admin/categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10); // Lấy 10 danh mục mỗi trang, sắp xếp mới nhất trước

        return response()->json([
            'message' => 'Lấy danh sách danh mục thành công.',
            'categories' => $categories
        ], 200);
    }

    /**
     * Tạo một danh mục mới.
     * POST /api/admin/categories
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Tên danh mục là bắt buộc.',
                'name.unique' => 'Tên danh mục này đã tồn tại.',
                'description.max' => 'Mô tả danh mục không được vượt quá 1000 ký tự.',
            ]);

            DB::beginTransaction();

            $category = Category::create([
                'name' => $request->name,
                // Slug sẽ được tự động tạo trong Category Model khi setNameAttribute được gọi
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Danh mục đã được tạo thành công.',
                'category' => $category
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi tạo danh mục.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị thông tin chi tiết của một danh mục.
     * GET /api/admin/categories/{category}
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category)
    {
        return response()->json([
            'message' => 'Lấy thông tin danh mục thành công.',
            'category' => $category
        ], 200);
    }

    /**
     * Cập nhật thông tin của một danh mục.
     * PUT/PATCH /api/admin/categories/{category}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Tên danh mục là bắt buộc.',
                'name.unique' => 'Tên danh mục này đã tồn tại.',
                'description.max' => 'Mô tả danh mục không được vượt quá 1000 ký tự.',
            ]);

            DB::beginTransaction();

            $category->update([
                'name' => $request->name,
                // Slug sẽ được tự động cập nhật trong Category Model
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Danh mục đã được cập nhật thành công.',
                'category' => $category
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật danh mục.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa một danh mục.
     * DELETE /api/admin/categories/{category}
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        try {
            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Danh mục đã được xóa thành công.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi xóa danh mục.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}