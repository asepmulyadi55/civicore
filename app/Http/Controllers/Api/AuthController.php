<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginRequest;
use App\Http\Resources\API\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request): JsonResponse
    {
        $key = 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();

        // Brute-force protection: 5 attempts per minute
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error("Too many login attempts. Please try again in {$seconds} seconds.", 429);
        }

        $user = User::with('role', 'block')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            return $this->error('Invalid credentials.', 401);
        }

        if (!$user->is_active) {
            return $this->error('Your account is inactive. Please contact an administrator.', 403);
        }

        RateLimiter::clear($key);

        // Revoke previous tokens from same device to enforce single-session per device
        $deviceName = $request->input('device_name', 'mobile');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return $this->success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user),
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return $this->noContent('Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role', 'block');

        return $this->success(new UserResource($user), 'User fetched successfully');
    }

    public function revokeAll(Request $request): JsonResponse
    {
        // Revoke all tokens for this user (sign out from all devices)
        $request->user()->tokens()->delete();

        return $this->noContent('All sessions revoked successfully');
    }
}
