<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Lấy danh sách tất cả bình luận/đánh giá (cho Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $reviews = Review::with('user', 'product')
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->input('per_page', 10));

        return response()->json($reviews);
    }

    /**
     * Xóa một bình luận/đánh giá (cho Admin).
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json(['message' => 'Bình luận đã được xóa bởi Admin.'], 200);
    }
}
