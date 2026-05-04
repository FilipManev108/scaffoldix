<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
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
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::middleware(['auth:sanctum', 'not.disabled'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend']);
});
