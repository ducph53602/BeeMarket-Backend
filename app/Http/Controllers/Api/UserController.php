<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of all users (for admin).
     * Admin API: GET /api/admin/users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Authorization handled by middleware 'role:admin'
        $users = User::paginate(10);
        return response()->json($users);
    }

    /**
     * Update the role of a specified user (for admin).
     * Admin API: PUT /api/admin/users/{user}/role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRole(Request $request, User $user)
    {
        // Authorization handled by middleware 'role:admin'
        $request->validate([
            'role' => ['required', 'string', Rule::in(['user', 'seller', 'admin'])],
        ]);

        // Prevent changing the role of the current admin user (self-demotion/promotion)
        if ($user->id === auth()->id() && $request->role !== $user->role) {
            return response()->json(['message' => 'Cannot change your own role via this API.'], 403);
        }

        $user->update(['role' => $request->role]);
        return response()->json($user);
    }
}
