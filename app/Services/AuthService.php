<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
        ]);

        // Assign default role
        $role = $data['role'] ?? 'customer';
        $user->assignRole($role);

        // Generate tokens
        $tokens = $this->generateTokens($user);

        return [
            'user' => $user->load('roles'),
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Login user
     */
    public function login(array $credentials): ?array
    {
        if (!$token = auth()->attempt($credentials)) {
            return null;
        }

        $user = auth()->user();

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return null;
        }

        // Generate refresh token
        $refreshToken = $this->createRefreshToken($user);

        return [
            'user' => $user->load('roles'),
            'access_token' => $token,
            'refresh_token' => $refreshToken->token,
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Refresh access token
     */
    public function refresh(string $refreshToken): ?array
    {
        $tokenModel = RefreshToken::where('token', $refreshToken)->first();

        if (!$tokenModel || $tokenModel->isExpired()) {
            return null;
        }

        $user = $tokenModel->user;

        if (!$user->is_active) {
            return null;
        }

        // Generate new tokens
        $newToken = JWTAuth::fromUser($user);
        $newRefreshToken = $this->createRefreshToken($user);

        // Revoke old refresh token
        $tokenModel->revoke();

        return [
            'access_token' => $newToken,
            'refresh_token' => $newRefreshToken->token,
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        // Revoke all refresh tokens
        RefreshToken::revokeAllForUser($user->id);

        // Invalidate current JWT token
        auth()->logout();
    }

    /**
     * Generate access and refresh tokens
     */
    protected function generateTokens(User $user): array
    {
        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
        ];
    }

    /**
     * Create refresh token
     */
    protected function createRefreshToken(User $user): RefreshToken
    {
        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => Str::random(128),
            'expires_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl')),
        ]);
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        return auth()->user();
    }
}