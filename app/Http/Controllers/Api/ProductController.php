<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Js;
use Illuminate\Support\Str; // For Str::slug
class ProductController extends Controller
{
    /**
     * Display a listing of products.
     * Public API: GET /api/products
     * Supports: search (q), category_id, min_price, max_price, sort_by, sort_order, pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'user', 'reviews']); // Eager load category and seller

        // Search by product name or description
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }
        
        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at'); // Default sort by creation date
        $sortOrder = $request->get('sort_order', 'desc'); // Default sort order descending

        // Validate sort_by to prevent SQL injection
        $allowedSorts = ['name', 'price', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $products = $query->paginate(12); // 12 products per page

        $products->getCollection()->transform(function ($product) {
            if ($product->image) {
                $product->image = Storage::url($product->image);
            }
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Display the specified product.
     * Public API: GET /api/products/{product:slug}
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        $product->load(['category', 'user', 'reviews.user']); 
        
        if ($product->image) {
            $product->image = Storage::url($product->image);
        }

        return response()->json($product);
    }

    /**
     * Store a newly created product in storage.
     * Seller API: POST /api/seller/products
     * Requires 'seller' or 'admin' role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorization: Only sellers or admins can create products
        if (!Auth::user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized to create product.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048', // Max 2MB, image file
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Store image in public storage and get its path
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Auth::user()->products()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image' => $imagePath, // Store the relative path
            'category_id' => $request->category_id,
        ]);

        // Return the product with its full image URL
        $product->image = Storage::url($product->image);
        $product->load('category', 'user');

        return response()->json($product, 201);
    }

    /**
     * Update the specified product in storage.
     * Seller API: PUT /api/seller/products/{product}
     * Only allows updating quantity by the product owner (seller/admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $productId)
    {
        // $query = Product::with(['category', 'user', 'reviews']);

        // Authorization: Only the product owner (seller/admin) can update
        // if (Auth::user()->id !== $product->user_id && !Auth::user()->isAdmin()) {
        //     return response()->json(['message' => 'Unauthorized to update this product.'], 403);
        // }

        // $request->validate([
        //     'quantity' => 'required|integer|min:0', // Only quantity can be updated by seller
        // ]);

        // $product->update([
        //     'quantity' => $request->quantity,
        // ]);

        // $product->load('category', 'user'); // Reload relationships after update

         $user = Auth::user();
        $product = Product::where('id', $productId)->where('user_id', $user->id)->first();

        if (!$product) {
            // If product not found or not owned by the current user
            return response()->json(['message' => 'Product not found or unauthorized.'], 404);
        }

        // Validate the request data
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image' => 'nullable|image|max:2048', // Image is optional for update
        ]);

        $data = $request->only(['name', 'description', 'price', 'quantity', 'category_id']);

        // Update slug if name is changed
        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }
// dd($data);
        $product->update($data);

        $product->image = Storage::url($product->image); // Return full URL
        $product->load('category'); // Load category for response

        return response()->json($product, 200);
    }

    /**
     * Remove the specified product from storage.
     * Seller API: DELETE /api/seller/products/{product}
     * Only allows deletion by the product owner (seller/admin).
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $productId)
    {
        $user = Auth::user();
        $product = Product::where('id', $productId)->where('user_id', $user->id)->first();

        if (!$product) {
            // If product not found or not owned by the current user
            return response()->json(['message' => 'Product not found or unauthorized.'], 404);
        }

        // Delete associated image file
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }

    /**
     * Display a listing of products owned by the authenticated seller.
     * Seller API: GET /api/seller/products
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerProducts()
    {
        // Authorization handled by middleware 'role:seller'
        $user = Auth::user();
        $products = $user->products()->with('category')->paginate(10);

        $products->getCollection()->transform(function ($product) {
            if ($product->image) {
                $product->image = Storage::url($product->image);
            }
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Display a listing of all products (for admin).
     * Admin API: GET /api/admin/products
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminProducts()
    {
        // Authorization handled by middleware 'role:admin'
        $products = Product::with(['category', 'user'])->paginate(10);
        return response()->json($products);
    }

    /**
     * Remove the specified product from storage by admin.
     * Admin API: DELETE /api/admin/products/{product}
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyByAdmin(int $productId)
    {
        // Authorization handled by middleware 'role:admin'
        // Delete associated image file
         $product = Product::withTrashed()->find($productId);

         if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->forceDelete();
        return response()->json(['message' => 'Product deleted by admin successfully.'], 200);
    }
}