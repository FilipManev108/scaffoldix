<?php

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ApiResponse::success([
        'app' => config('app.name'),
        'environment' => app()->environment(),
    ], 'API is running');
});