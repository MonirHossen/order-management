<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
         return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $request,
        ], 201);

        $result = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $result,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or account is inactive',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $result,
        ]);
    }

    /**
     * Refresh access token
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->authService->refresh($request->refresh_token);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => $result,
        ]);
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        $user = auth()->user();
        $this->authService->logout($user);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(): JsonResponse
    {
        
        // $user = $this->authService->getAuthenticatedUser();

        return response()->json([
            'success' => true,
            'data' => 'dfdfd',
            // 'data' => $user->load('roles', 'permissions'),
        ]);
    }
}