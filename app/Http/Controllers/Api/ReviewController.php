<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Lấy tất cả đánh giá cho một sản phẩm cụ thể.
     * GET /api/products/{product}/reviews
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Product $product)
    {
        $reviews = $product->reviews()->with('user')->latest()->paginate(10);

        return response()->json([
            'message' => 'Lấy danh sách đánh giá thành công.',
            'product_id' => $product->id,
            'reviews' => $reviews
        ], 200);
    }

    /**
     * Gửi đánh giá và bình luận cho một sản phẩm đã mua.
     * POST /api/reviews
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'product_id.required' => 'ID sản phẩm là bắt buộc.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'rating.required' => 'Số sao đánh giá là bắt buộc.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Số sao đánh giá phải từ 1 đến 5.',
            'rating.max' => 'Số sao đánh giá phải từ 1 đến 5.',
            'comment.max' => 'Bình luận không được vượt quá 1000 ký tự.',
        ]);

        $productId = $request->product_id;

        DB::beginTransaction();
        try {
            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Đánh giá sản phẩm thành công.',
                'review' => $review
            ], 201); 

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi gửi đánh giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật một đánh giá cụ thể.
     * PUT/PATCH /api/reviews/{review}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Review $review)
    {
        if (Auth::id() !== $review->user_id) {
            return response()->json(['message' => 'Bạn không có quyền chỉnh sửa đánh giá này.'], 403);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5', // 'sometimes' nghĩa là không bắt buộc phải có
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Số sao đánh giá phải từ 1 đến 5.',
            'rating.max' => 'Số sao đánh giá phải từ 1 đến 5.',
            'comment.max' => 'Bình luận không được vượt quá 1000 ký tự.',
        ]);

        DB::beginTransaction();
        try {
            $review->update([
                'rating' => $request->input('rating', $review->rating), // Nếu không gửi rating, giữ nguyên giá trị cũ
                'comment' => $request->input('comment', $review->comment), // Nếu không gửi comment, giữ nguyên giá trị cũ
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cập nhật đánh giá thành công.',
                'review' => $review
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật đánh giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa một đánh giá cụ thể.
     * DELETE /api/reviews/{review}
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        if (Auth::id() !== $review->user_id) {
            return response()->json(['message' => 'Bạn không có quyền xóa đánh giá này.'], 403);
        }

        DB::beginTransaction();
        try {
            $review->delete();

            DB::commit();

            return response()->json([
                'message' => 'Xóa đánh giá thành công.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi xóa đánh giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}