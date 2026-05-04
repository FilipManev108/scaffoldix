<?php

use App\Http\Controllers\Api\AuthController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::success([
        'app' => config('app.name'),
        'environment' => app()->environment(),
    ], 'API is running');
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
