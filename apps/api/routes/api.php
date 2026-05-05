<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskStatusController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\WorkspaceController;
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

    Route::apiResource('workspaces', WorkspaceController::class);
    Route::apiResource('workspaces.teams', TeamController::class);
    Route::apiResource('workspaces.projects', ProjectController::class);
    Route::apiResource('projects.task-statuses', TaskStatusController::class)
        ->parameters(['task-statuses' => 'taskStatus']);
    Route::apiResource('projects.tasks', TaskController::class);
    Route::apiResource('tasks.comments', CommentController::class);
});
