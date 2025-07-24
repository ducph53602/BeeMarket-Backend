<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of all users (Admin only).
     * GET /api/admin/users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::paginate(10); 

        return response()->json([
            'message' => 'Lấy danh sách người dùng thành công.',
            'users' => $users
        ], 200);
    }

    /**
     * Update a user's role (Admin only).
     * PUT/PATCH /api/admin/users/{user}/role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRole(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->isAdmin()) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }

        if (Auth::id() === $user->id) {
            return response()->json(['message' => 'Bạn không thể thay đổi vai trò của chính mình.'], 403);
        }

        $request->validate([
            'role' => ['required', 'string', Rule::in(['user', 'seller', 'admin'])],
        ]);

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Cập nhật vai trò người dùng thành công.',
            'user' => $user
        ], 200);
    }

    /**
     * Optionally, you might want to view a single user's details (Admin only).
     * GET /api/admin/users/{user}
     */
    public function show(User $user)
    {
        return response()->json([
            'message' => 'Lấy thông tin người dùng thành công.',
            'user' => $user
        ], 200);
    }
}