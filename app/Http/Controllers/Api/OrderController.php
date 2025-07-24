<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Tạo một đơn hàng mới từ giỏ hàng hiện tại của người dùng.
     * POST /api/orders
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để đặt hàng.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var \App\Models\Cart|null $cart */
        $cart = $user->cart;

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng của bạn trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi đặt.'], 400);
        }

        try {
            $validatedData = $request->validate([
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_phone' => ['required', 'string', 'max:20'],
                'shipping_address' => ['required', 'string', 'max:500'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];
            $productsToUpdateStock = []; 

            foreach ($cart->cartItems as $cartItem) {
                /** @var \App\Models\Product $product */
                $product = Product::find($cartItem->product_id);

                if (!$product || $product->status !== 'active' || $product->stock < $cartItem->quantity) {
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Không thể tạo đơn hàng. Sản phẩm "' . ($product->name ?? 'Không xác định') . '" không tồn tại, không hoạt động hoặc không đủ số lượng tồn kho.',
                        'product_id' => $cartItem->product_id,
                        'available_stock' => $product->stock ?? 0,
                        'requested_quantity' => $cartItem->quantity
                    ], 400);
                }

                $itemPrice = $product->price; 
                $subtotal = $itemPrice * $cartItem->quantity;
                $totalAmount += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name, 
                    'price' => $itemPrice,             
                    'quantity' => $cartItem->quantity,
                ];

                $productsToUpdateStock[] = [
                    'product' => $product,
                    'quantity_ordered' => $cartItem->quantity
                ];
            }

            /** @var \App\Models\Order $order */
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . Str::upper(Str::random(10)), // Tạo mã đơn hàng duy nhất
                'total_amount' => $totalAmount,
                'status' => 'pending', 
                'customer_name' => $validatedData['customer_name'],
                'customer_email' => $user->email, 
                'customer_phone' => $validatedData['customer_phone'],
                'shipping_address' => $validatedData['shipping_address'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->orderItems()->create($itemData);
            }

            foreach ($productsToUpdateStock as $item) {
                $item['product']->decrement('stock', $item['quantity_ordered']);
            }

            $cart->cartItems()->delete();
            $cart->delete();

            DB::commit(); 

            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công.',
                'order' => $order->load('orderItems.product') // Tải quan hệ để trả về đầy đủ thông tin
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi xác thực thông tin đơn hàng.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Đã xảy ra lỗi không mong muốn khi tạo đơn hàng. Vui lòng thử lại sau.',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    /**
     * Hiển thị danh sách các đơn hàng của người dùng hiện tại.
     * GET /api/orders
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem đơn hàng.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Order::with('user', 'orderItems.product'); // Load thông tin user và order items, sản phẩm

        if ($user->isAdmin()) {
            if ($request->has('user_id')) {
                $request->validate(['user_id' => 'required|exists:users,id']);
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('seller_id')) {
                $request->validate(['seller_id' => 'required|exists:users,id']);
                $sellerId = $request->seller_id;
                $query->whereHas('orderItems.product', function ($q) use ($sellerId) {
                    $q->where('user_id', $sellerId); // user_id của product là người bán
                });
            }
        } elseif ($user->isSeller()) {
            $query->whereHas('orderItems.product', function ($q) use ($user) {
                $q->where('user_id', $user->id); // user_id của product là user đang đăng nhập (seller)
            });
        } else { 
            $query->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $request->validate([
                'status' => ['string', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'refunded'])],
            ]);
            $query->where('status', $request->status);
        }

        /** @var \App\Models\Order[] $orders */
        $orders = $query->latest()->paginate(10);

        return response()->json([
            'message' => 'Danh sách đơn hàng của bạn.',
            'orders' => $orders
        ], 200);
    }

    /**
     * Hiển thị chi tiết một đơn hàng cụ thể.
     * GET /api/orders/{order}
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem đơn hàng.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $order->load('orderItems.product');

        if ($user->isAdmin() || $order->user_id === $user->id || ($user->isSeller() && $order->orderItems->contains(function($item) use ($user) {
            return $item->product->user_id === $user->id; // Kiểm tra nếu sản phẩm thuộc về người bán này
        }))) {
            return response()->json([
                'message' => 'Lấy thông tin đơn hàng thành công.',
                'order' => $order
            ], 200);
        }

        return response()->json(['message' => 'Bạn không có quyền xem đơn hàng này.'], 403);
    }

    /**
     * Cập nhật trạng thái của một đơn hàng.
     * PUT /api/orders/{order}/status
     * Chỉ dành cho Người bán hoặc Admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để cập nhật đơn hàng.'], 401);
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isSeller()) {
            return response()->json(['message' => 'Bạn không có quyền cập nhật trạng thái đơn hàng.'], 403);
        }

        if ($user->isSeller() && !$user->isAdmin()) {
            $hasSellerProduct = $order->orderItems()->whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();

            if (!$hasSellerProduct) {
                return response()->json(['message' => 'Bạn không có quyền cập nhật trạng thái cho đơn hàng này.'], 403);
            }
        }

        try {
            $request->validate([
                'status' => ['required', 'string', Rule::in(['pending', 'processing', 'completed', 'cancelled', 'refunded'])],
            ]);

            DB::beginTransaction(); 

            $order->status = $request->status;
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Trạng thái đơn hàng đã được cập nhật thành công.',
                'order' => $order->load('orderItems.product')
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack(); 
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu đầu vào.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Lỗi cập nhật trạng thái đơn hàng: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Đã xảy ra lỗi không mong muốn khi cập nhật trạng thái đơn hàng. Vui lòng thử lại sau.',
                'error' => $e->getMessage() 
            ], 500);
        }
    }
}