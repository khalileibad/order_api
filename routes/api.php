<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\OrderController;

// ==================== Public Routes ====================
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
	Route::get('refresh', [AuthController::class, 'refresh']);
});
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'index']);
});
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'index']);
});

// ==================== All User Routes ====================
Route::middleware(['auth:api', 'role:customer,admin'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    
});

// ==================== Admin Routes ====================
Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'new_category']);
        Route::post('/{id}', [CategoryController::class, 'update_category']);
        Route::delete('/{id}', [CategoryController::class, 'delete_category']);
	});
    
	// Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'new_product']);
        Route::post('/{id}', [ProductController::class, 'update_product']);
        Route::delete('/{id}', [ProductController::class, 'delete_product']);
	});
    
});

// ==================== Customer Routes ====================
Route::prefix('orders')->middleware(['auth:api', 'role:customer'])->group(function () {
	Route::get('/', [OrderController::class, 'index']);
    Route::get('/{order_id}', [OrderController::class, 'index']);
	Route::post('/create', [OrderController::class, 'new_order']);
	Route::post('/{order_id}', [OrderController::class, 'update_order']);
	//Route::post('/{order_id}/cancel', [OrderController::class, 'cancelOrder']);
});

Route::prefix('payments')->middleware(['auth:api', 'role:customer'])->group(function () {
	Route::get('/gateway', [PaymentController::class, 'index']);
	Route::get('/gateway/{id}', [PaymentController::class, 'show']);
	
	Route::post('/initiate/{order_id}', [PaymentController::class, 'initiate']);
	Route::post('/process/{order_id}', [PaymentController::class, 'processPayment']);
	//Route::post('/process/{order_id}', [PaymentController::class, 'processPayment']);
	//Route::get('/status/{transaction_id}', [TransactionController::class, 'getPaymentStatus']);
    //Route::post('/refund', [TransactionController::class, 'refundPayment']);
    
});