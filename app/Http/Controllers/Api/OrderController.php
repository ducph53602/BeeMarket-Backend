<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated user (customer).
     * User API: GET /api/orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrders()
    {
        $orders = Auth::user()->orders()->with('orderItems.product')->latest()->paginate(10);
        return response()->json($orders);
    }

    /**
     * Display the specified order for the authenticated user.
     * User API: GET /api/orders/{order}
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        // Authorization: Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to view this order.'], 403);
        }

        $order->load('orderItems.product');
        return response()->json($order);
    }

    /**
     * Display a listing of orders related to the authenticated seller's products.
     * Seller API: GET /api/seller/orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerOrders()
    {
        // Authorization handled by middleware 'role:seller'
        $sellerId = Auth::id();

        // Get orders that contain products sold by this seller
        $orders = Order::whereHas('orderItems.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })
        ->with(['user', 'orderItems' => function ($query) use ($sellerId) {
            // Load only order items related to this seller's products
            $query->whereHas('product', function ($q) use ($sellerId) {
                $q->where('user_id', $sellerId);
            })->with('product');
        }])
        ->latest()
        ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Cancel a specific order (by seller or admin).
     * Seller API: POST /api/seller/orders/{order}/cancel
     * Admin API: POST /api/admin/orders/{order}/cancel (Admin can also cancel using this route)
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Order $order)
    {
        $user = Auth::user();

        // Authorization:
        // Seller can cancel orders that contain THEIR products.
        // Admin can cancel ANY order.
        $isSellerOfOrder = $order->orderItems->contains(function ($item) use ($user) {
            return $item->product->user_id === $user->id;
        });

        if (!$user->isAdmin() && !$isSellerOfOrder) {
            return response()->json(['message' => 'Unauthorized to cancel this order.'], 403);
        }

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return response()->json(['message' => 'Order cannot be cancelled in its current status.'], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = 'cancelled';
            $order->save();

            // Refund stock for each item in the cancelled order
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->save();
                }
            }

            DB::commit();
            return response()->json(['message' => 'Order cancelled successfully.', 'order' => $order], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of all orders (for admin).
     * Admin API: GET /api/admin/orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminOrders()
    {
        // Authorization handled by middleware 'role:admin'
        $orders = Order::with(['user', 'orderItems.product'])->latest()->paginate(10);
        return response()->json($orders);
    }

    /**
     * Remove the specified order from storage by admin.
     * Admin API: DELETE /api/admin/orders/{order}
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyByAdmin(Order $order)
    {
        // Authorization handled by middleware 'role:admin'
        // Optionally, refund stock if order was not cancelled previously
        if ($order->status !== 'cancelled') {
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->save();
                }
            }
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted by admin successfully.'], 200);
    }
}