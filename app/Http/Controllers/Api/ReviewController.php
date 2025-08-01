<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for a specific product.
     * Public API: GET /api/products/{product:slug}/reviews
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function productReviews(Product $product)
    {
        $reviews = $product->reviews()->with('user')->latest()->paginate(5);
        return response()->json($reviews);
    }

    /**
     * Store a newly created review in storage.
     * User API: POST /api/products/{product:slug}/reviews
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Product $product)
    {
        // Authorization: Only authenticated users can review
        // (Handled by auth:sanctum middleware on the route group)

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if the user has already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this product.'], 409);
        }

        $review = $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user'); // Load user relationship for the response
        return response()->json($review, 201);
    }

    /**
     * Update the specified review in storage.
     * User API: PUT /api/reviews/{review}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Review $review)
    {
        // Authorization: Only the owner of the review can update it
        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to update this review.'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user');
        return response()->json($review);
    }

    /**
     * Remove the specified review from storage.
     * User API: DELETE /api/reviews/{review}
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        // Authorization: Only the owner of the review or an admin can delete it
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized to delete this review.'], 403);
        }

        $review->delete();
        return response()->json(['message' => 'Review deleted successfully.'], 200);
    }

    /**
     * Display a listing of reviews for products owned by the authenticated seller.
     * Seller API: GET /api/seller/reviews
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerReviews()
    {
        // Authorization handled by middleware 'role:seller'
        $sellerId = Auth::id();

        $reviews = Review::whereHas('product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })
        ->with(['user', 'product'])
        ->latest()
        ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Display a listing of all reviews (for admin).
     * Admin API: GET /api/admin/reviews
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminReviews()
    {
        // Authorization handled by middleware 'role:admin'
        $reviews = Review::with(['user', 'product'])->latest()->paginate(10);
        return response()->json($reviews);
    }

    /**
     * Remove the specified review from storage by admin.
     * Admin API: DELETE /api/admin/reviews/{review}
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyByAdmin(Review $review)
    {
        // Authorization handled by middleware 'role:admin'
        $review->delete();
        return response()->json(['message' => 'Review deleted by admin successfully.'], 200);
    }
}