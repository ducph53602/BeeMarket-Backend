<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display the authenticated user's cart.
     * User API: GET /api/cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $user = Auth::user();
        $cart = $user->cart()->with('cartItems.product')->firstOrCreate([]); // Create cart if not exists

        // Calculate total amount for the cart
        $totalAmount = $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'cart' => $cart,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Add a product to the authenticated user's cart.
     * User API: POST /api/cart/add
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart()->firstOrCreate([]);
        $product = Product::find($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock for this product.'], 400);
        }

        $cartItem = $cart->cartItems()->firstOrNew(['product_id' => $request->product_id]);
        $cartItem->quantity += $request->quantity;
        $cartItem->save();

        $cart->load('cartItems.product'); // Reload to get updated data
        return response()->json($cart, 200);
    }

    /**
     * Update the quantity of a product in the cart.
     * User API: PUT /api/cart/update/{cartItem}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Authorization: Ensure the cart item belongs to the authenticated user's cart
        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to update this cart item.'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $product = $cartItem->product;
        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock for this product.'], 400);
        }

        if ($request->quantity === 0) {
            $cartItem->delete();
        } else {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
        }

        $cartItem->cart->load('cartItems.product'); // Reload cart to get updated data
        return response()->json($cartItem->cart, 200);
    }

    /**
     * Remove a product from the cart.
     * User API: DELETE /api/cart/remove/{cartItem}
     *
     * @param  \App\Models\CartItem  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(CartItem $cartItem)
    {
        // Authorization: Ensure the cart item belongs to the authenticated user's cart
        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to remove this cart item.'], 403);
        }

        $cartItem->delete();
        $cartItem->cart->load('cartItems.product'); // Reload cart to get updated data
        return response()->json($cartItem->cart, 200);
    }

    /**
     * Checkout the cart and create an order.
     * User API: POST /api/cart/checkout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = $user->cart()->with('cartItems.product')->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        // Validate stock before creating order
        foreach ($cart->cartItems as $item) {
            if ($item->product->quantity < $item->quantity) {
                return response()->json(['message' => "Not enough stock for product: {$item->product->name}"], 400);
            }
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => 0, // Will be updated after adding items
                'status' => 'pending',
                // You might want to add shipping_address and phone_number here from request
                // 'shipping_address' => $request->shipping_address,
                // 'phone_number' => $request->phone_number,
            ]);

            foreach ($cart->cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price, // Price at the time of purchase
                ]);

                // Deduct stock from product
                $product = $item->product;
                $product->quantity -= $item->quantity;
                $product->save();

                $totalAmount += ($item->quantity * $item->product->price);
            }

            $order->total_amount = $totalAmount;
            $order->save();

            // Clear the cart after successful checkout
            $cart->cartItems()->delete();
            $cart->delete();

            DB::commit();
            return response()->json(['message' => 'Order placed successfully!', 'order' => $order->load('orderItems.product')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to place order: ' . $e->getMessage()], 500);
        }
    }
}
