<?php

use App\Http\Controllers\Api\Auth\OTPAuthController;
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

// Product Search
Route::get('/products/search', [\App\Http\Controllers\Frontend\ProductSearchController::class, 'search']);

// Campaigns (Public)
Route::get('/campaigns', [\App\Http\Controllers\Api\CampaignController::class, 'index']);
Route::get('/campaigns/featured', [\App\Http\Controllers\Api\CampaignController::class, 'featured']);
Route::get('/campaigns/{slug}', [\App\Http\Controllers\Api\CampaignController::class, 'show']);

// Size Charts (Public)
Route::get('/size-charts', [\App\Http\Controllers\Api\SizeChartController::class, 'index']);
Route::get('/size-charts/{id}', [\App\Http\Controllers\Api\SizeChartController::class, 'show']);
Route::get('/size-charts/category/{slug}', [\App\Http\Controllers\Api\SizeChartController::class, 'byCategory']);

// Product Reviews (Public - View)
Route::get('/products/{productId}/reviews', [\App\Http\Controllers\Frontend\ReviewController::class, 'index']);
Route::post('/reviews/{reviewId}/helpful', [\App\Http\Controllers\Frontend\ReviewController::class, 'helpful'])->middleware('throttle:10,1');

// Related Products
Route::get('/products/{productId}/related', [\App\Http\Controllers\Frontend\RelatedProductController::class, 'index']);
Route::get('/products/{productId}/frequently-bought', [\App\Http\Controllers\Frontend\RelatedProductController::class, 'frequentlyBoughtTogether']);

// Shipping Calculator (Public)
Route::post('/shipping/calculate', [\App\Http\Controllers\Frontend\ShippingController::class, 'calculate']);
Route::get('/shipping/methods', [\App\Http\Controllers\Frontend\ShippingController::class, 'methods']);

// Coupon Validation (Public)
Route::post('/coupons/validate', [\App\Http\Controllers\Frontend\CouponController::class, 'validate']);

// Guest Checkout (Public)
Route::post('/guest-checkout', [\App\Http\Controllers\Frontend\GuestCheckoutController::class, 'store'])->name('api.guest-checkout')->middleware('throttle:5,1');

// Payment Callbacks (Public - Webhook endpoints)
Route::post('/payment/aamarpay/success', [\App\Http\Controllers\Frontend\PaymentController::class, 'aamarPaySuccess'])->name('api.payment.aamarpay.success')->middleware('throttle:20,1');
Route::post('/payment/aamarpay/fail', [\App\Http\Controllers\Frontend\PaymentController::class, 'aamarPayFail'])->name('api.payment.aamarpay.fail')->middleware('throttle:20,1');
Route::post('/payment/aamarpay/cancel', [\App\Http\Controllers\Frontend\PaymentController::class, 'aamarPayCancel'])->name('api.payment.aamarpay.cancel')->middleware('throttle:20,1');

// Sliders / Banners (Frontend)
Route::get('/sliders', [SliderController::class, 'index']);

// Products API (Enhanced)
Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('/products/slug/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'bySlug']);
Route::get('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
Route::post('/products/check-availability', [\App\Http\Controllers\Api\ProductController::class, 'checkAvailability']);
Route::get('/products/{product}/price', [\App\Http\Controllers\Api\ProductController::class, 'getPrice']);
Route::post('/products/{product}/find-variant', [\App\Http\Controllers\Api\ProductController::class, 'findVariant']);

// Inventory API (Public)
Route::get('/inventory/summary', [\App\Http\Controllers\Api\InventoryController::class, 'summary']);
Route::get('/inventory/low-stock', [\App\Http\Controllers\Api\InventoryController::class, 'lowStock']);
Route::get('/inventory/out-of-stock', [\App\Http\Controllers\Api\InventoryController::class, 'outOfStock']);
Route::get('/inventory/movements', [\App\Http\Controllers\Api\InventoryController::class, 'movements']);

// Tracking (Public)
Route::get('/tracking', [\App\Modules\Courier\Http\Controllers\TrackingController::class, 'track']);

// Auth Routes (Unified JWT)
Route::group(['prefix' => 'auth'], function () {
    // Public auth routes with rate limiting
    Route::post('/register', [OTPAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/mobile/send-otp', [OTPAuthController::class, 'sendOTP'])->middleware('throttle:3,1');
    Route::post('/mobile/verify', [OTPAuthController::class, 'verifyOTP'])->middleware('throttle:5,1');
    Route::post('/password/reset', [OTPAuthController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::post('/login', [JWTAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/refresh', [JWTAuthController::class, 'refresh'])->middleware('throttle:10,1');

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
    Route::get('/users/profile', [\App\Modules\User\Http\Controllers\ProfileController::class, 'show']);
    Route::put('/users/profile', [\App\Modules\User\Http\Controllers\ProfileController::class, 'update']);

    // Addresses
    Route::get('/users/addresses', [\App\Modules\User\Http\Controllers\AddressController::class, 'index']);
    Route::post('/users/addresses', [\App\Modules\User\Http\Controllers\AddressController::class, 'store']);
    Route::put('/users/addresses/{id}', [\App\Modules\User\Http\Controllers\AddressController::class, 'update']);
    Route::delete('/users/addresses/{id}', [\App\Modules\User\Http\Controllers\AddressController::class, 'destroy']);
    Route::patch('/users/addresses/{id}/default', [\App\Modules\User\Http\Controllers\AddressController::class, 'setDefault']);

    // Cart
    Route::get('/cart', [\App\Modules\Sales\Http\Controllers\CartController::class, 'index']);
    Route::post('/cart/items', [\App\Modules\Sales\Http\Controllers\CartController::class, 'store']);
    Route::put('/cart/items/{id}', [\App\Modules\Sales\Http\Controllers\CartController::class, 'update']);
    Route::delete('/cart/items/{id}', [\App\Modules\Sales\Http\Controllers\CartController::class, 'destroy']);
    Route::post('/cart/apply-coupon', [\App\Modules\Sales\Http\Controllers\CartController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [\App\Modules\Sales\Http\Controllers\CartController::class, 'removeCoupon']);
    Route::get('/cart/summary', [\App\Modules\Sales\Http\Controllers\CartController::class, 'summary']);
    Route::delete('/cart/clear', [\App\Modules\Sales\Http\Controllers\CartController::class, 'clear']);

    // Orders
    Route::get('/orders', [\App\Modules\Sales\Http\Controllers\OrderController::class, 'index']);
    Route::post('/orders', [\App\Modules\Sales\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders/{id}', [\App\Modules\Sales\Http\Controllers\OrderController::class, 'show']);
    Route::get('/orders/{id}/track', [\App\Modules\Sales\Http\Controllers\OrderController::class, 'track']);
    Route::post('/orders/{id}/cancel', [\App\Modules\Sales\Http\Controllers\OrderController::class, 'cancel']);

    // Wishlist
    Route::get('/wishlist', [\App\Modules\Sales\Http\Controllers\WishlistController::class, 'index']);
    Route::post('/wishlist', [\App\Modules\Sales\Http\Controllers\WishlistController::class, 'store']);
    Route::delete('/wishlist/{productId}', [\App\Modules\Sales\Http\Controllers\WishlistController::class, 'destroy']);
    Route::get('/wishlist/{productId}/check', [\App\Modules\Sales\Http\Controllers\WishlistController::class, 'check']);
    Route::post('/wishlist/toggle', [\App\Modules\Sales\Http\Controllers\WishlistController::class, 'toggle']);

    // Notifications
    Route::get('/notifications', [\App\Modules\Notification\Http\Controllers\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Modules\Notification\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::get('/notifications/unread-count', [\App\Modules\Notification\Http\Controllers\NotificationController::class, 'unreadCount']);

    // Product Reviews (Write - Auth Required)
    Route::post('/products/{productId}/reviews', [\App\Http\Controllers\Frontend\ReviewController::class, 'store']);

    // Available Coupons (Auth Required for user-specific)
    Route::get('/coupons/available', [\App\Http\Controllers\Frontend\CouponController::class, 'available']);

    // Payment (Auth Required)
    Route::post('/orders/{orderId}/payment/initiate', [\App\Http\Controllers\Frontend\PaymentController::class, 'initiate']);
    Route::get('/orders/{orderId}/payment/status', [\App\Http\Controllers\Frontend\PaymentController::class, 'status']);

    // Admin Routes
    Route::group(['prefix' => 'admin', 'middleware' => 'role:admin'], function () {
        // Dashboard
        Route::get('/dashboard', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'dashboard']);

        // Reports
        Route::get('/reports/sales', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'sales']);
        Route::get('/reports/inventory', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'inventory']);
        Route::get('/reports/customers', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'customers']);
        Route::get('/reports/coupons', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'coupons']);
        Route::post('/reports/export', [\App\Modules\Report\Http\Controllers\Admin\ReportController::class, 'export']);
        
        // Inventory Management API
        Route::get('/inventory/valuation', [\App\Http\Controllers\Api\InventoryController::class, 'valuation']);
        Route::post('/inventory/variants/{variant}/stock', [\App\Http\Controllers\Api\InventoryController::class, 'updateStock']);
        Route::post('/inventory/bulk-update', [\App\Http\Controllers\Api\InventoryController::class, 'bulkUpdate']);
        Route::get('/inventory/variants/{variant}/history', [\App\Http\Controllers\Api\InventoryController::class, 'variantHistory']);
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
