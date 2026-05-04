<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return ApiResponse::success(
            [
                'user' => $this->safeUserData($user),
            ],
            'User registered successfully',
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error(
                'Invalid credentials',
                [
                    'email' => ['The provided credentials are incorrect.'],
                ],
                401
            );
        }

        if ($user->disabled_at !== null) {
            return ApiResponse::error(
                'Account is disabled',
                [
                    'account' => ['This account has been disabled.'],
                ],
                403
            );
        }

        Auth::login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ApiResponse::success(
            [
                'user' => $this->safeUserData($request->user()),
            ],
            'Logged in successfully'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::forgetGuards();

        return ApiResponse::success(
            null,
            'Logged out successfully'
        );
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            [
                'user' => $this->safeUserData($request->user()),
            ],
            'Authenticated user retrieved successfully'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function safeUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
