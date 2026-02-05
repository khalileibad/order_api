<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\Admin\ProductController;

// ==================== Public Routes ====================
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
	Route::get('refresh', [AuthController::class, 'refresh']);
});

// ==================== All User Routes ====================
Route::middleware(['auth:api', 'role:customer,admin'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    
});

// ==================== Admin Routes ====================
Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
    
    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/export', [ProductController::class, 'export']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::patch('/{id}/stock', [ProductController::class, 'updateStock']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        
        // استعادة وحذف نهائي
        Route::post('/{id}/restore', [ProductController::class, 'restore']);
        Route::delete('/{id}/force', [ProductController::class, 'forceDelete']);
    });
    
});