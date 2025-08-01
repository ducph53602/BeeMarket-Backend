<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage; // For image URLs

class OrderPageController extends Controller
{
    /**
     * Display the list of user's orders.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return view('orders.index');
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng.');
        }

        $orders = [];
        $errorMessage = null;

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return redirect()->route('login')->with('error', 'Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
            }

            $response = Http::withToken($token)->get(config('app.api_url') . '/orders');

            if ($response->successful()) {
                $orders = $response->json()['data'] ?? []; // Assuming paginated data
                // Transform product images in order items to full URLs
                $orders = collect($orders)->map(function ($order) {
                    if (isset($order['order_items'])) {
                        $order['order_items'] = collect($order['order_items'])->map(function ($item) {
                            if (isset($item['product']['image'])) {
                                $item['product']['image'] = Storage::url($item['product']['image']);
                            }
                            return $item;
                        })->all();
                    }
                    return $order;
                })->all();
            } else {
                $errorMessage = $response->json()['message'] ?? 'Không thể tải đơn hàng.';
                Log::error('Error fetching orders: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Lỗi kết nối khi tải đơn hàng.';
            Log::error('Connection error fetching orders: ' . $e->getMessage());
        }

        return view('orders.index', compact('orders', 'errorMessage'));
    }

    /**
     * Display the details of a specific order.
     *
     * @param  int  $orderId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(int $orderId)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem chi tiết đơn hàng.');
        }

        $order = null;
        $errorMessage = null;

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return redirect()->route('login')->with('error', 'Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
            }

            $response = Http::withToken($token)->get(config('app.api_url') . '/orders/' . $orderId);

            if ($response->successful()) {
                $orderData = $response->json();
                // Transform product images in order items to full URLs
                if (isset($orderData['order_items'])) {
                    $orderData['order_items'] = collect($orderData['order_items'])->map(function ($item) {
                        if (isset($item['product']['image'])) {
                            $item['product']['image'] = Storage::url($item['product']['image']);
                        }
                        return $item;
                    })->all();
                }
                $order = $orderData;
            } else {
                $errorMessage = $response->json()['message'] ?? 'Không tìm thấy đơn hàng hoặc có lỗi xảy ra.';
                Log::error("Error fetching order details for ID {$orderId}: " . $errorMessage);
                if ($response->status() === 404) {
                    abort(404, $errorMessage);
                } else {
                    abort(500, $errorMessage);
                }
            }
        } catch (\Exception $e) {
            $errorMessage = 'Lỗi kết nối khi tải chi tiết đơn hàng.';
            Log::error("Connection error fetching order details for ID {$orderId}: " . $e->getMessage());
            abort(500, $errorMessage);
        }

        return view('orders.show', compact('order', 'errorMessage'));
    }
}
