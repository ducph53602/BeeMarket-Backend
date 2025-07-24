<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::orderBy('order', 'asc')->paginate(10);

        return response()->json([
            'message' => 'Lấy danh sách banner thành công.',
            'banners' => $banners
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/admin/banners
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'required|image|max:2048', // Bắt buộc, file ảnh, max 2MB
                'title' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'link' => 'nullable|url|max:255', // Phải là URL hợp lệ
                'is_active' => 'boolean',
                'order' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            $imagePath = $request->file('image')->store('banners', 'public'); // Lưu ảnh vào storage/app/public/banners
            $validatedData['image_path'] = $imagePath; // Lưu đường dẫn vào database

            $banner = Banner::create($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'Banner đã được tạo thành công.',
                'banner' => $banner
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi tạo banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'message' => 'Lấy thông tin banner thành công.',
            'banner' => $banner
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|image|max:2048', // Có thể cập nhật ảnh
                'title' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'link' => 'nullable|url|max:255',
                'is_active' => 'boolean',
                'order' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            // Xử lý upload ảnh mới (nếu có)
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                    Storage::disk('public')->delete($banner->image_path);
                }
                $imagePath = $request->file('image')->store('banners', 'public');
                $validatedData['image_path'] = $imagePath;
            }

            $banner->update($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'Banner đã được cập nhật thành công.',
                'banner' => $banner
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        DB::beginTransaction();
        try {
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $banner->delete();

            DB::commit();

            return response()->json([
                'message' => 'Banner đã được xóa thành công.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi xóa banner.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách các banner đang hoạt động để hiển thị công khai trên trang chủ.
     * GET /api/banners (Public route)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex()
    {
        $banners = Banner::where('is_active', true)
                         ->orderBy('order', 'asc')
                         ->orderBy('created_at', 'desc')
                         ->get(); 

        return response()->json([
            'message' => 'Lấy danh sách banner công khai thành công.',
            'banners' => $banners
        ], 200);
    }
}
