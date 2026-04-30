<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'data' => [
            'app' => config('app.name'),
            'environment' => app()->environment(),
        ],
    ]);
});