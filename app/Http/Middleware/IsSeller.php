<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->is_seller) {
            // Nếu không phải, chuyển hướng hoặc trả về lỗi
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này.');
            // Hoặc: abort(403, 'Unauthorized.'); // Trả về lỗi 403 Forbidden
        }
        return $next($request);
    }
}
