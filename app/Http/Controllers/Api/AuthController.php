<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
            ]);

            /** @var \App\Models\User $user */
            $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'user',
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address' => $validatedData['address'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->only(['id', 'name', 'email', 'phone_number', 'address', 'is_seller', 'is_admin']),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201); 
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422); 
        }catch (QueryException $e) {
            return response()->json([
                'message' => 'Database Error: Could not register user.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        } 
    }

    /**
     * Authenticate the user and generate a token.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (! Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'], // Thông báo lỗi chung chung để tăng cường bảo mật
                ]);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Logged in successfully.',
                'user' => $user->only(['id', 'name', 'email', 'phone_number', 'address', 'is_seller', 'is_admin']),
                'token' => $token,
                'token_type' => 'Bearer', 
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log the user out (revoke their token).
     */
    public function logout(Request $request)
    {
        // Thu hồi token hiện tại của người dùng
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get the authenticated user's details.
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}