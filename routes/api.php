<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SellerRequestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\Admin\CategoryImageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->middleware('auth:sanctum');
Route::patch('/products/{id}/moderate', [ProductController::class, 'moderate'])->middleware(['auth:sanctum', 'admin']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store'])->middleware('auth:sanctum', 'admin');
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('auth:sanctum', 'admin');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum', 'admin');

Route::get('/seller-requests', [SellerRequestController::class, 'index'])->middleware('auth:sanctum', 'admin');
Route::post('/seller-requests', [SellerRequestController::class, 'store'])->middleware('auth:sanctum');
Route::put('/seller-requests/{id}', [SellerRequestController::class, 'update'])->middleware('auth:sanctum', 'admin');

Route::get('/carts', [CartController::class, 'index'])->middleware('auth:sanctum');
Route::post('/carts', [CartController::class, 'store'])->middleware('auth:sanctum');
Route::get('/carts/{id}', [CartController::class, 'show'])->middleware('auth:sanctum');

Route::get('/cart-items', [CartItemController::class, 'index'])->middleware('auth:sanctum');
Route::post('/cart-items', [CartItemController::class, 'store'])->middleware('auth:sanctum');
Route::get('/cart-items/{id}', [CartItemController::class, 'show'])->middleware('auth:sanctum');
Route::put('/cart-items/{id}', [CartItemController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/cart-items/{id}', [CartItemController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum');
Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:sanctum');
Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('auth:sanctum');
Route::put('/orders/{id}', [OrderController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/seller/orders', [OrderController::class, 'sellerOrders'])->middleware('auth:sanctum', 'seller');
Route::post('/checkout', [OrderController::class, 'checkout'])->middleware('auth:sanctum');

Route::get('/order-items', [OrderItemController::class, 'index'])->middleware('auth:sanctum');
Route::post('/order-items', [OrderItemController::class, 'store'])->middleware('auth:sanctum');
Route::get('/order-items/{id}', [OrderItemController::class, 'show'])->middleware('auth:sanctum');
Route::put('/order-items/{id}', [OrderItemController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/order-items/{id}', [OrderItemController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/admin/users', [AdminController::class, 'users'])->middleware('auth:sanctum', 'admin');
Route::get('/admin/orders', [AdminController::class, 'orders'])->middleware('auth:sanctum', 'admin');
Route::put('/admin/categories/{category}/image', [CategoryImageController::class, 'update'])->middleware(['auth:sanctum', 'admin']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);