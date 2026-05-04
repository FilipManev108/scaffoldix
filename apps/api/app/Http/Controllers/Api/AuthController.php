<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        return ApiResponse::success(
            null,
            'Registration endpoint is available'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return ApiResponse::success(
            null,
            'Login endpoint is available'
        );
    }

    public function logout(): JsonResponse
    {
        return ApiResponse::success(
            null,
            'Logout endpoint is available'
        );
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            [
                'user' => $request->user(),
            ],
            'Authenticated user endpoint is available'
        );
    }
}
