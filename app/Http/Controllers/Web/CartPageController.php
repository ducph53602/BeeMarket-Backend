<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage; // For image URLs

class CartPageController extends Controller
{
    /**
     * Display the user's cart.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return view('cart.index');

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng.');
        }

        $cartItems = [];
        $totalAmount = 0;
        $errorMessage = null;

        try {
            // Ensure user has a token before making API call
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return redirect()->route('login')->with('error', 'Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
            }

            $response = Http::withToken($token)->get(config('app.api_url') . '/cart');

            if ($response->successful()) {
                $data = $response->json();
                $cartItems = $data['cart']['cart_items'] ?? [];
                $totalAmount = $data['total_amount'] ?? 0;

                // Transform product images to full URLs
                $cartItems = collect($cartItems)->map(function ($item) {
                    if (isset($item['product']['image'])) {
                        $item['product']['image'] = Storage::url($item['product']['image']);
                    }
                    return $item;
                })->all();

            } else {
                $errorMessage = $response->json()['message'] ?? 'Không thể tải giỏ hàng.';
                Log::error('Error fetching cart: ' . $errorMessage);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Lỗi kết nối khi tải giỏ hàng.';
            Log::error('Connection error fetching cart: ' . $e->getMessage());
        }

        return view('cart.index', compact('cartItems', 'totalAmount', 'errorMessage'));
    }

    /**
     * Add a product to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if (!Auth::check()) {
            return redirect()->back()->with('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.');
        }

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return redirect()->back()->with('error', 'Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
            }

            $response = Http::withToken($token)
                            ->post(config('app.api_url') . '/cart/add', [
                                'product_id' => $request->product_id,
                                'quantity' => $request->quantity,
                            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
            } else {
                $message = $response->json()['message'] ?? 'Không thể thêm vào giỏ hàng.';
                return redirect()->back()->with('error', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi kết nối khi thêm vào giỏ hàng.');
        }
    }

    /**
     * Update a cart item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized. Please login.'], 401);
        }

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return response()->json(['message' => 'Authentication token not found. Please login again.'], 401);
            }

            $response = Http::withToken($token)
                            ->put(config('app.api_url') . '/cart/update/' . $cartItem, [
                                'quantity' => $request->quantity,
                            ]);

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            } else {
                $message = $response->json()['message'] ?? 'Không thể cập nhật giỏ hàng.';
                return response()->json(['message' => $message], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi kết nối khi cập nhật giỏ hàng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove a cart item.
     *
     * @param  int  $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(int $cartItem)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized. Please login.'], 401);
        }

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return response()->json(['message' => 'Authentication token not found. Please login again.'], 401);
            }

            $response = Http::withToken($token)
                            ->delete(config('app.api_url') . '/cart/remove/' . $cartItem);

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            } else {
                $message = $response->json()['message'] ?? 'Không thể xóa sản phẩm khỏi giỏ hàng.';
                return response()->json(['message' => $message], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi kết nối khi xóa sản phẩm khỏi giỏ hàng: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process checkout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        try {
            $token = Auth::user()->currentAccessToken?->token;
            if (!$token) {
                return redirect()->back()->with('error', 'Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
            }

            $response = Http::withToken($token)
                            ->post(config('app.api_url') . '/cart/checkout');

            if ($response->successful()) {
                return redirect()->route('orders.index')->with('success', 'Đơn hàng đã được đặt thành công!');
            } else {
                $message = $response->json()['message'] ?? 'Thanh toán thất bại.';
                return redirect()->back()->with('error', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi kết nối khi thanh toán.');
        }
    }
}
