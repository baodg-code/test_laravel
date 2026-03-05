<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::get('/up', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
});

Route::get('/docs/api-docs.json', function () {
    $path = storage_path('api-docs/api-docs.json');

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::resource('products', ProductController::class)->only(['index']);
    Route::get('/product-detail/{product}', [ProductController::class, 'detail'])->name('product-detail.index');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->except(['show']);
    });
});
