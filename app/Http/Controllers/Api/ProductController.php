<?php

namespace App\Http\Controllers\Api; 

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;         
use Illuminate\Validation\Rule;     
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products (publicly accessible).
     * This is for regular users to see all active products with search functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // $query = Product::with('category', 'user'); 

        // if ($request->has('search')) {
        //     $search = $request->search;
        //     $query->where(function ($q) use ($search) {
        //         $q->where('name', 'like', '%' . $search . '%')
        //           ->orWhere('description', 'like', '%' . $search . '%');
        //     });
        // }

        //  if ($request->has('category_id')) {
        //     $request->validate([
        //         'category_id' => 'sometimes|exists:categories,id',
        //     ]);
        //     $query->where('category_id', $request->category_id);
        // }

        // if ($request->has('category_slug')) {
        //     $category = Category::where('slug', $request->category_slug)->first();
        //     if ($category) {
        //         $query->where('category_id', $category->id);
        //     } else {
        //         return response()->json(['message' => 'Danh mục không tồn tại.'], 404);
        //     }
        // }
        
        // if ($request->has('min_price')) {
        //     $query->where('price', '>=', $request->min_price);
        // }
        // if ($request->has('max_price')) {
        //     $query->where('price', '<=', $request->max_price);
        // }

        // $sortBy = $request->input('sort_by', 'created_at'); // Giá trị mặc định là 'created_at'
        // $sortOrder = $request->input('sort_order', 'desc'); // Giá trị mặc định là 'desc' (giảm dần)

        // $validSortColumns = ['name', 'price', 'created_at'];
        // if (!in_array($sortBy, $validSortColumns)) {
        //     $sortBy = 'created_at'; 
        // }

        // if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
        //     $sortOrder = 'desc'; 
        // }

        // $query->orderBy($sortBy, $sortOrder);

        // if ($request->has('in_stock')) {
        //     if (filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN)) { // Chuyển đổi chuỗi "true"/"false" sang boolean
        //         $query->where('stock', '>', 0);
        //     } else {
        //         $query->where('stock', '=', 0);
        //     }
        // }

        // $products = $query->where('status', 'active') 
        //                   ->latest() 
        //                   ->paginate(10); 

        $products = Product::with('category', 'user')
                       ->where('status', 'active')
                       ->latest()
                       ->take(10) // Lấy chỉ 10 sản phẩm
                       ->get(); // Dùng get() thay vì paginate() tạm thời

    // Log kết quả để kiểm tra dữ liệu trước khi trả về
    Log::info('API Products Response Data:', ['products_count' => $products->count(), 'first_product_category' => $products->first()->category->name ?? 'N/A']);
        return response()->json([
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'products' => [ // Vẫn giữ cấu trúc phân trang nếu bạn có ý định sử dụng nó sau này
            'data' => $products->toArray(), // Chuyển Collection sang array
            // Không có links hay meta nếu dùng get()
        ]
        ]);
    }

    /**
     * Lấy danh sách tất cả sản phẩm (bao gồm cả active/inactive) cho Admin.
     * GET /api/admin/products
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminIndex(Request $request)
    {
        $query = Product::with('category', 'user'); 

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('category_slug')) {
            $category = Category::where('slug', $request->category_slug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                return response()->json(['message' => 'Danh mục không tồn tại.'], 404);
            }
        }

        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->input('sort_by', 'created_at'); 
        $sortOrder = $request->input('sort_order', 'desc'); 

        $validSortColumns = ['name', 'price', 'created_at', 'status', 'stock']; 
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'created_at'; 
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc'; 
        }
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(10); 

        return response()->json([
            'message' => 'Lấy danh sách tất cả sản phẩm thành công.',
            'products' => $products
        ]);
    }

    /**
     * Display the specified product.
     * Accessible publicly, but with checks for inactive products.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     * @param  \App\Models\User  $user
     */
    public function show(Product $product)
    {
        $product->load('category', 'user');

        if ($product->status !== 'active' && (!Auth::check() || ($product->user_id !== Auth::id() && !Auth::user()->isAdmin()))) {
            return response()->json(['message' => 'Sản phẩm không tìm thấy hoặc không hoạt động.'], 404);
        }
        return response()->json([
            'message' => 'Lấy thông tin sản phẩm thành công.',
            'product' => $product
        ], 200);
    }

    /**
     * Display a listing of products owned by the authenticated seller/admin.
     * This is specifically for sellers to manage ONLY their products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerProducts()
    {
        $user = Auth::user();

        if (!$user->isSeller()) {
            return response()->json(['message' => 'Bạn không có quyền truy cập chức năng này.'], 403);
        }

        $products = Product::with('category')
                           ->where('user_id', $user->id)
                           ->latest()
                           ->paginate(10); 

        return response()->json([
            'message' => 'Lấy danh sách sản phẩm của người bán thành công.',
            'products' => $products
        ], 200);
    }

    /**
     * Store a newly created product in storage by a seller/admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSeller()) {
            return response()->json(['message' => 'Bạn không có quyền tạo sản phẩm.'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'condition' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048', 
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])], 
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public'); 
            $validatedData['image_path'] = $imagePath;
        }

        $validatedData['slug'] = Str::slug($validatedData['name']) . '-' . rand(1000, 9999);
        $validatedData['user_id'] = $user->id;

        $product = Product::create($validatedData);

        return response()->json([
            'message' => 'Sản phẩm đã được tạo thành công.',
            'product' => $product
        ], 201);
    }

    /**
     * Update the specified product in storage by its owner or an admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product  (Laravel tự động inject model Product dựa trên ID)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $user = Auth::user();

    if ($product->user_id !== $user->id && !$user->isAdmin()) {
        return response()->json(['message' => 'Bạn không có quyền cập nhật sản phẩm này.'], 403);
    }

    // DEBUG: In ra toàn bộ request để xem Laravel nhận được gì
    dd($request->all());

    $validatedData = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'sometimes|required|numeric|min:0',
        'stock' => 'sometimes|required|integer|min:0',
        'category_id' => 'nullable|exists:categories,id',
        'condition' => 'nullable|string|max:255',
        'location' => 'nullable|string|max:255',
        'image' => 'nullable|image|max:2048', 
        'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
    ]);

    // DEBUG: In ra dữ liệu sau khi validate để xem những trường nào được chấp nhận
    // dd($validatedData)

        if ($request->hasFile('image')) {
            // Tùy chọn: Xóa ảnh cũ nếu tồn tại
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $validatedData['image_path'] = $imagePath;
        } elseif (!isset($validatedData['image']) && ($request->isMethod('put') || $request->isMethod('patch'))) {
            // Nếu không có ảnh mới và đang cập nhật, bỏ qua trường ảnh
            unset($validatedData['image']); 
        }

        if (isset($validatedData['name'])) {
             $validatedData['slug'] = Str::slug($validatedData['name']) . '-' . rand(1000, 9999);
        }

        if (empty($validatedData)) {
            return response()->json(['message' => 'Không có dữ liệu để cập nhật.'], 400);
        }

        $product->update($validatedData);

        return response()->json([
            'message' => 'Sản phẩm đã được cập nhật thành công.',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified product from storage by its owner or an admin.
     *
     * @param  \App\Models\Product  $product 
     * @return \Illuminate\Http\JsonResponse
     * @param  \App\Models\User  $user
     */
    public function destroy(Product $product)
    {
        /**
         * @var mixed
         */
        $user = Auth::user();

        if ($product->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Bạn không có quyền xóa sản phẩm này.'], 403);
        }

        // Tùy chọn: Xóa ảnh liên quan khi xóa sản phẩm
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json(['message' => 'Sản phẩm đã được xóa thành công.'], 200);
    }
}