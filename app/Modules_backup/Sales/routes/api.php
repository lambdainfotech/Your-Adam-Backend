<?php

declare(strict_types=1);

use App\Modules\Sales\Http\Controllers\CartController;
use App\Modules\Sales\Http\Controllers\OrderController;
use App\Modules\Sales\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales Module API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/summary', [CartController::class, 'summary']);
    Route::post('/cart/items', [CartController::class, 'store']);
    Route::put('/cart/items/{id}', [CartController::class, 'update']);
    Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Wishlist Routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
});

// Public tracking route
Route::get('/v1/orders/{orderNumber}/track', [OrderController::class, 'track']);
