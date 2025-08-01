<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /**
     * Lấy danh sách tất cả đơn hàng (cho Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $orders = Order::with('user', 'orderItems.product')
                       ->orderBy('created_at', 'desc')
                       ->paginate($request->input('per_page', 10));

        return response()->json($orders);
    }

    /**
     * Lấy chi tiết một đơn hàng (cho Admin).
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        $order->load('user', 'orderItems.product');
        return response()->json($order);
    }

    /**
     * Cập nhật trạng thái đơn hàng (cho Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,completed,cancelled',
        ]);

        // Nếu trạng thái chuyển sang 'cancelled', hoàn lại số lượng sản phẩm vào kho
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            DB::beginTransaction();
            try {
                foreach ($order->orderItems as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
                $order->status = $request->status;
                $order->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Lỗi khi cập nhật trạng thái và hoàn lại kho: ' . $e->getMessage()], 500);
            }
        } else {
            $order->status = $request->status;
            $order->save();
        }

        return response()->json(['message' => 'Trạng thái đơn hàng đã được cập nhật.', 'order' => $order], 200);
    }

    /**
     * Xóa một đơn hàng (cho Admin).
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        DB::beginTransaction();
        try {
            // Hoàn lại số lượng sản phẩm vào kho nếu đơn hàng chưa bị hủy
            if ($order->status !== 'cancelled' && $order->status !== 'completed') {
                foreach ($order->orderItems as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                    }
                }
            }
            $order->orderItems()->delete(); // Xóa các mục trong đơn hàng
            $order->delete(); // Xóa đơn hàng

            DB::commit();
            return response()->json(['message' => 'Đơn hàng đã được xóa bởi Admin.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi khi xóa đơn hàng: ' . $e->getMessage()], 500);
        }
    }
}
