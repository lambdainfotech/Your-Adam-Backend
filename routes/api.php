<?php

use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Frontend\SiteInfoController;
use App\Http\Controllers\Frontend\SliderController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Unified JWT Authentication
|--------------------------------------------------------------------------
|
| All protected routes use JWT authentication via 'jwt.auth' middleware.
| API uses Authorization header for token transmission.
|
*/

// Health check
Route::get('/health', function (): JsonResponse {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// Public Site Info (no authentication required)
Route::get('/site/info', [SiteInfoController::class, 'index']);
Route::get('/site/navigation', [\App\Http\Controllers\Frontend\NavigationController::class, 'index']);
Route::get('/homepage', [\App\Http\Controllers\Frontend\HomepageController::class, 'index']);
Route::get('/categories', [\App\Http\Controllers\Frontend\CategoryController::class, 'index']);
Route::get('/categories/{slug}', [\App\Http\Controllers\Frontend\CategoryController::class, 'show']);

// API Version Prefix
Route::group(['prefix' => 'v1'], function () {

    // Public Routes
    Route::get('/categories', [\App\Modules\Catalog\src\Http\Controllers\CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [\App\Modules\Catalog\src\Http\Controllers\CategoryController::class, 'show']);
    
    // Sliders / Banners (Frontend)
    Route::get('/sliders', [SliderController::class, 'index']);
    
    // Products API (Enhanced)
    Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/products/slug/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'bySlug']);
    Route::get('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::post('/products/check-availability', [\App\Http\Controllers\Api\ProductController::class, 'checkAvailability']);
    Route::get('/products/{product}/price', [\App\Http\Controllers\Api\ProductController::class, 'getPrice']);
    Route::post('/products/{product}/find-variant', [\App\Http\Controllers\Api\ProductController::class, 'findVariant']);
    
    // Legacy product routes
    Route::get('/products/search', [\App\Modules\Catalog\src\Http\Controllers\ProductController::class, 'search']);

    // Inventory API (Public)
    Route::get('/inventory/summary', [\App\Http\Controllers\Api\InventoryController::class, 'summary']);
    Route::get('/inventory/low-stock', [\App\Http\Controllers\Api\InventoryController::class, 'lowStock']);
    Route::get('/inventory/out-of-stock', [\App\Http\Controllers\Api\InventoryController::class, 'outOfStock']);
    Route::get('/inventory/movements', [\App\Http\Controllers\Api\InventoryController::class, 'movements']);

    // Tracking (Public)
    Route::get('/tracking', [\App\Modules\Courier\src\Http\Controllers\TrackingController::class, 'track']);

    // Auth Routes (Unified JWT)
    Route::group(['prefix' => 'auth'], function () {
        // Public auth routes
        Route::post('/mobile/send-otp', [\App\Modules\Auth\src\Http\Controllers\AuthController::class, 'sendOTP']);
        Route::post('/mobile/verify', [\App\Modules\Auth\src\Http\Controllers\AuthController::class, 'verifyOTP']);
        Route::post('/login', [JWTAuthController::class, 'login']);
        Route::post('/refresh', [JWTAuthController::class, 'refresh']);

        // Protected auth routes
        Route::middleware('jwt.auth')->group(function () {
            Route::post('/logout', [JWTAuthController::class, 'logout']);
            Route::get('/me', [JWTAuthController::class, 'me']);
            Route::get('/check', [JWTAuthController::class, 'check']);
        });
    });

    // Protected Routes (JWT required)
    Route::middleware('jwt.auth')->group(function () {

        // User Profile
        Route::get('/users/profile', [\App\Modules\User\src\Http\Controllers\ProfileController::class, 'show']);
        Route::put('/users/profile', [\App\Modules\User\src\Http\Controllers\ProfileController::class, 'update']);

        // Addresses
        Route::get('/users/addresses', [\App\Modules\User\src\Http\Controllers\AddressController::class, 'index']);
        Route::post('/users/addresses', [\App\Modules\User\src\Http\Controllers\AddressController::class, 'store']);
        Route::put('/users/addresses/{id}', [\App\Modules\User\src\Http\Controllers\AddressController::class, 'update']);
        Route::delete('/users/addresses/{id}', [\App\Modules\User\src\Http\Controllers\AddressController::class, 'destroy']);
        Route::patch('/users/addresses/{id}/default', [\App\Modules\User\src\Http\Controllers\AddressController::class, 'setDefault']);

        // Cart
        Route::get('/cart', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'index']);
        Route::post('/cart/items', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'store']);
        Route::put('/cart/items/{id}', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'update']);
        Route::delete('/cart/items/{id}', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'destroy']);
        Route::post('/cart/apply-coupon', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'applyCoupon']);
        Route::delete('/cart/coupon', [\App\Modules\Sales\src\Http\Controllers\CartController::class, 'removeCoupon']);

        // Orders
        Route::get('/orders', [\App\Modules\Sales\src\Http\Controllers\OrderController::class, 'index']);
        Route::post('/orders', [\App\Modules\Sales\src\Http\Controllers\OrderController::class, 'store']);
        Route::get('/orders/{id}', [\App\Modules\Sales\src\Http\Controllers\OrderController::class, 'show']);
        Route::get('/orders/{id}/track', [\App\Modules\Sales\src\Http\Controllers\OrderController::class, 'track']);
        Route::post('/orders/{id}/cancel', [\App\Modules\Sales\src\Http\Controllers\OrderController::class, 'cancel']);

        // Wishlist
        Route::get('/wishlist', [\App\Modules\Sales\src\Http\Controllers\WishlistController::class, 'index']);
        Route::post('/wishlist', [\App\Modules\Sales\src\Http\Controllers\WishlistController::class, 'store']);
        Route::delete('/wishlist/{productId}', [\App\Modules\Sales\src\Http\Controllers\WishlistController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [\App\Modules\Notification\src\Http\Controllers\NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [\App\Modules\Notification\src\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::get('/notifications/unread-count', [\App\Modules\Notification\src\Http\Controllers\NotificationController::class, 'unreadCount']);

        // Admin Routes
        Route::group(['prefix' => 'admin', 'middleware' => 'role:admin'], function () {
            // Dashboard
            Route::get('/dashboard', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'dashboard']);

            // Reports
            Route::get('/reports/sales', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'sales']);
            Route::get('/reports/inventory', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'inventory']);
            Route::get('/reports/customers', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'customers']);
            Route::get('/reports/coupons', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'coupons']);
            Route::post('/reports/export', [\App\Modules\Report\src\Http\Controllers\Admin\ReportController::class, 'export']);
            
            // Inventory Management API
            Route::get('/inventory/valuation', [\App\Http\Controllers\Api\InventoryController::class, 'valuation']);
            Route::post('/inventory/variants/{variant}/stock', [\App\Http\Controllers\Api\InventoryController::class, 'updateStock']);
            Route::post('/inventory/bulk-update', [\App\Http\Controllers\Api\InventoryController::class, 'bulkUpdate']);
            Route::get('/inventory/variants/{variant}/history', [\App\Http\Controllers\Api\InventoryController::class, 'variantHistory']);
        });
    });
});

// Fallback route
Route::fallback(function (): JsonResponse {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'error_code' => 'ROUTE_NOT_FOUND',
    ], 404);
});
