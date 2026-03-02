<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductExportApiController;
use App\Http\Controllers\Api\ProductDetailApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::get('/menu-items', [PublicProductController::class, 'index']);

Route::get('/categories', [PublicCategoryController::class, 'index']);
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/product-detail/{product}', [ProductDetailApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    Route::post('/orders/checkout', [OrderApiController::class, 'store']);
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);

    Route::post('/exports/products', [ProductExportApiController::class, 'store']);
    Route::get('/exports/products/{productExport}', [ProductExportApiController::class, 'show']);
    Route::get('/exports/products/{productExport}/download', [ProductExportApiController::class, 'download']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/categories', [CategoryApiController::class, 'index']);
        Route::get('/products', [ProductApiController::class, 'index']);
        Route::get('/users', [AdminUserApiController::class, 'index']);
    });

    Route::apiResource('categories', CategoryApiController::class)->except(['index', 'show']);
    Route::apiResource('products', ProductApiController::class)->except(['index', 'show']);
});
