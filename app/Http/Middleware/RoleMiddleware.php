<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // Nếu chưa đăng nhập, trả về lỗi 401 Unauthorized
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user = Auth::user();

        // Kiểm tra xem người dùng có bất kỳ vai trò nào trong danh sách cho phép không
        if (!in_array($user->role, $roles)) {
            // Nếu không có quyền, trả về lỗi 403 Forbidden
            return response()->json(['message' => 'Forbidden. You do not have the required role.'], 403);
        }

        return $next($request);
    }
}
