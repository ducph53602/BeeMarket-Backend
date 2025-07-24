<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Lấy hoặc tạo giỏ hàng cho người dùng/khách.
     *
     * @param Request $request
     * @return Cart
     */
    protected function getOrCreateCart(Request $request): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => null] 
            );
        } else {
            $sessionId = $request->session()->getId(); 

            $cart = Cart::where('session_id', $sessionId)->first();
            if ($cart && !$cart->user_id) {
                return $cart; 
            }
            return Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['user_id' => null] 
            );
        }
    }
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewCart(Request $request)
    {
        /** @var \App\Models\Cart $cart */
        $cart = $this->getOrCreateCart($request);

        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\CartItem[] $cartItems */
        $cartItems = $cart->cartItems()->with('product')->get();

        $totalAmount = $cartItems->sum(function($item) {
            return $item->quantity * ($item->product->price ?? 0);
        });

        return response()->json([
            'cart_id' => $cart->id,
            'items' => $cartItems->map(function ($item) {
                return [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Sản phẩm không tồn tại',
                    'product_price' => $item->product->price ?? 0,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->quantity * ($item->product->price ?? 0),
                    'product_image' => $item->product->image ?? null, 
                    'product_stock' => $item->product->stock ?? 0, 
                ];
            }),
            'total_amount' => $totalAmount,
            'message' => 'Lấy thông tin giỏ hàng thành công.'
        ], 200);
    }

    /**
     * Add a product to the authenticated user's/guest's cart.
     * Tên phương thức đổi từ store thành addToCart.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'quantity' => ['required', 'integer', 'min:1'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi xác thực',
                'errors' => $e->errors()
            ], 422);
        }

        $product = Product::find($validatedData['product_id']);

        if (!$product || $product->status !== 'active' || $product->stock <= 0) {
            return response()->json(['message' => 'Sản phẩm không tồn tại, không hoạt động hoặc đã hết hàng.'], 404);
        }

        $cart = $this->getOrCreateCart($request);

        $cartItem = $cart->cartItems()->firstOrNew(['product_id' => $product->id]);

        $currentCartQuantity = $cartItem->exists ? $cartItem->quantity : 0;
        $requestedQuantity = $validatedData['quantity'];
        $newQuantity = $currentCartQuantity + $requestedQuantity;

        if ($product->stock < $newQuantity) {
            return response()->json([
                'message' => 'Số lượng thêm vào vượt quá số lượng tồn kho hiện có.',
                'current_stock' => $product->stock,
                'current_quantity_in_cart' => $currentCartQuantity,
                'requested_quantity_to_add' => $requestedQuantity,
                'total_quantity_after_add' => $newQuantity
            ], 400);
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        return response()->json([
            'message' => 'Sản phẩm đã được thêm/cập nhật số lượng trong giỏ hàng thành công.',
            'cart_item' => $cartItem->load('product'), 
            'cart_id' => $cart->id
        ], 200); 
    }

    /**
     * Update the specified resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CartItem $cartItem)
    {
        try {
            $validatedData = $request->validate([
                'quantity' => ['required', 'integer', 'min:0'], 
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu đầu vào.',
                'errors' => $e->errors()
            ], 422);
        }

        $userCart = $this->getOrCreateCart($request);
        if ($cartItem->cart_id !== $userCart->id) {
            return response()->json([
                'message' => 'Không được phép: Mục giỏ hàng không thuộc về giỏ hàng của bạn.'
            ], 403); 
        }

        $product = $cartItem->product; 

        if (!$product || $product->status !== 'active') {
            return response()->json(['message' => 'Sản phẩm liên quan không tồn tại hoặc không hoạt động.'], 404);
        }

        $requestedQuantity = $validatedData['quantity'];
        $oldQuantityInCart = $cartItem->quantity;

        if ($request->quantity === 0) {
            $cartItem->delete();
            return response()->json([
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng.'
            ], 200);
        }

        if ($request->quantity > $product->stock + $cartItem->getOriginal('quantity')) {
             return response()->json([
                 'message' => 'Số lượng yêu cầu vượt quá số lượng tồn kho hiện có.',
                 'available_stock' => $product->stock
             ], 400);
        }

        $stockAvailableForUpdate = $product->stock + $oldQuantityInCart;

        if ($requestedQuantity > $stockAvailableForUpdate) {
            return response()->json([
                'message' => 'Số lượng yêu cầu vượt quá số lượng tồn kho hiện có.',
                'current_stock_on_hand' => $product->stock, 
                'current_quantity_in_cart' => $oldQuantityInCart, 
                'maximum_possible_quantity_in_cart' => $stockAvailableForUpdate
            ], 400);
        }

        if ($requestedQuantity !== $oldQuantityInCart) {
            $cartItem->quantity = $requestedQuantity;
            $cartItem->save(); 
        } else {
            return response()->json([
                'message' => 'Số lượng không thay đổi, không cần cập nhật.'
            ], 200);
        }

        return response()->json([
            'message' => 'Số lượng mặt hàng trong giỏ hàng đã được cập nhật thành công.',
            'cart_item' => $cartItem->load('product')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *  
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        $userCart = $this->getOrCreateCart($request);
        if ($cartItem->cart_id !== $userCart->id) {
            return response()->json([
                'message' => 'Không được phép: Mục giỏ hàng không thuộc về giỏ hàng của bạn.'
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng thành công.'
        ]);
    }

    /**
     * Clear the cart for the authenticated user or guest.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $cartItems = $cart->cartItems;

        foreach ($cartItems as $item) {
            $item->delete(); 
        }

        return response()->json([
            'message' => 'Giỏ hàng đã được làm sạch thành công.'
        ]);
    }

    /**
     * Merge guest cart with authenticated user's cart.
     * This method assumes that the guest cart is identified by the session ID.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mergeCarts(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Người dùng chưa được xác thực.'], 401);
        }

        $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $guestCart = Cart::where('session_id', $request->session()->getId())->first();

        if ($guestCart && $guestCart->id !== $userCart->id) {
            $guestCart->load('cartItems.product');
            foreach ($guestCart->cartItems as $guestItem) {
                $product = $guestItem->product;

                if (!$product || $product->status !== 'active' || $product->stock <= 0) {
                    Log::warning("Skipping guest cart item {$guestItem->id} for product {$guestItem->product_id} due to invalid product or zero stock.");
                    continue; 
                }

                /** @var \App\Models\CartItem $userCartItem */
                $currentQuantityInUserCart = $userCartItem->exists ? $userCartItem->quantity : 0;
                $quantityToMerge = $guestItem->quantity;
                $totalQuantityAfterMerge = $currentQuantityInUserCart + $quantityToMerge;

                $userCartItem = $userCart->cartItems()->firstOrNew(['product_id' => $product->id]);

                if ($totalQuantityAfterMerge > ($product->stock + $currentQuantityInUserCart)) { 
                    $userCartItem->quantity = $product->stock + $currentQuantityInUserCart; 
                    Log::warning("Merged guest cart item {$guestItem->id} for product {$guestItem->product_id} with limited quantity due to stock constraint. Max allowed: {$userCartItem->quantity}");
                } else {
                    $userCartItem->quantity = $totalQuantityAfterMerge;
                }

                $userCartItem->save();
            }

            $guestCart->cartItems()->delete();
            $guestCart->delete();
            
            return response()->json(['message' => 'Giỏ hàng khách đã được hợp nhất thành công.'], 200);
        }

        return response()->json(['message' => 'Không có giỏ hàng khách để hợp nhất hoặc giỏ hàng đã được hợp nhất.'], 200);
    }
}
