<?php

use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\StockInController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\OrderFulfillmentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SizeChartController;
use App\Http\Controllers\Admin\PredefinedDescriptionController;
use App\Http\Controllers\Admin\BulkOperationsController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\BrandValueController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ExpenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes - Unified JWT Authentication
|--------------------------------------------------------------------------
|
| All routes use JWT authentication via 'jwt.auth' middleware.
| Web interface uses HTTP-Only cookies for token storage.
|
*/

// Guest routes (no auth required)
Route::middleware(['web'])->group(function () {
    Route::get('/login', [JWTAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [JWTAuthController::class, 'login'])->name('admin.login.post');
    Route::post('/refresh', [JWTAuthController::class, 'refresh'])->name('admin.refresh');
});

// Protected routes (JWT auth + admin role required)
Route::middleware(['web', 'jwt.auth', 'role:admin,super-admin'])->group(function () {
    Route::post('/logout', [JWTAuthController::class, 'logout'])->name('admin.logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Products
    Route::resource('products', ProductController::class)->names('admin.products');
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('admin.products.toggle-status');
    Route::post('products/{product}/toggle-bestseller', [ProductController::class, 'toggleBestSeller'])->name('admin.products.toggle-bestseller');
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('admin.products.duplicate');
    Route::post('products/{product}/quick-update-stock', [ProductController::class, 'quickUpdateStock'])->name('admin.products.quick-update-stock');

    // Categories
    Route::resource('categories', CategoryController::class)->names('admin.categories');
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('admin.categories.toggle-status');

    // Attributes Management
    Route::resource('attributes', AttributeController::class)->names('admin.attributes');
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'addValue'])->name('admin.attributes.values.add');
    Route::put('attribute-values/{value}', [AttributeController::class, 'updateValue'])->name('admin.attributes.values.update');
    Route::delete('attribute-values/{value}', [AttributeController::class, 'deleteValue'])->name('admin.attributes.values.delete');

    // Product Variants - Enhanced
    Route::get('products/{product}/variants', [ProductVariantController::class, 'index'])->name('admin.products.variants');
    Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generate'])->name('admin.products.variants.generate');
    Route::post('products/{product}/variants/preview', [ProductVariantController::class, 'previewCombinations'])->name('admin.products.variants.preview');
    Route::post('products/{product}/variants/add', [ProductVariantController::class, 'addVariant'])->name('admin.products.variants.add');
    Route::post('products/{product}/variants/reorder', [ProductVariantController::class, 'reorder'])->name('admin.products.variants.reorder');
    Route::post('products/{product}/attributes', [ProductVariantController::class, 'updateAttributes'])->name('admin.products.attributes.update');
    
    // Individual Variant Actions
    Route::get('variants/{variant}/edit', [ProductVariantController::class, 'getVariant'])->name('admin.variants.edit');
    Route::put('variants/{variant}', [ProductVariantController::class, 'updateVariant'])->name('admin.variants.update');
    Route::patch('variants/{variant}/quick-update', [ProductVariantController::class, 'quickUpdate'])->name('admin.variants.quick-update');
    Route::post('variants/{variant}/toggle-status', [ProductVariantController::class, 'toggleStatus'])->name('admin.variants.toggle-status');
    Route::delete('variants/{variant}', [ProductVariantController::class, 'deleteVariant'])->name('admin.variants.delete');
    Route::post('variants/{variant}/image', [ProductVariantController::class, 'updateImage'])->name('admin.variants.update-image');

    // Product Images
    Route::get('products/{product}/images', [\App\Http\Controllers\Admin\ProductImageController::class, 'index'])->name('admin.products.images');
    Route::post('products/{product}/images', [\App\Http\Controllers\Admin\ProductImageController::class, 'store'])->name('admin.products.images.store');
    Route::put('products/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductImageController::class, 'update'])->name('admin.products.images.update');
    Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductImageController::class, 'destroy'])->name('admin.products.images.destroy');
    Route::post('products/{product}/images/{image}/main', [\App\Http\Controllers\Admin\ProductImageController::class, 'setMain'])->name('admin.products.images.main');
    Route::post('products/{product}/images/reorder', [\App\Http\Controllers\Admin\ProductImageController::class, 'reorder'])->name('admin.products.images.reorder');

    // Size Charts
    Route::resource('size-charts', SizeChartController::class)->names('admin.size-charts');
    Route::post('size-charts/{size_chart}/toggle-status', [SizeChartController::class, 'toggleStatus'])->name('admin.size-charts.toggle-status');

    // Predefined Descriptions
    Route::resource('predefined-descriptions', PredefinedDescriptionController::class)->names('admin.predefined-descriptions');
    Route::post('predefined-descriptions/reorder', [PredefinedDescriptionController::class, 'reorder'])->name('admin.predefined-descriptions.reorder');
    Route::get('predefined-descriptions/by-type', [PredefinedDescriptionController::class, 'getByType'])->name('admin.predefined-descriptions.by-type');

    // Orders
    Route::resource('orders', OrderController::class)->names('admin.orders');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('admin.orders.invoice');
    Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('admin.orders.print');
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::post('orders/{order}/update-payment-status', [OrderController::class, 'updatePaymentStatus'])->name('admin.orders.update-payment-status');
    
    // Order Fulfillment
    Route::post('orders/{order}/assign-courier', [OrderFulfillmentController::class, 'assignCourier'])->name('admin.orders.assign-courier');
    Route::post('orders/{order}/mark-shipped', [OrderFulfillmentController::class, 'markShipped'])->name('admin.orders.mark-shipped');
    Route::post('orders/{order}/mark-delivered', [OrderFulfillmentController::class, 'markDelivered'])->name('admin.orders.mark-delivered');
    Route::get('orders/{order}/tracking', [OrderFulfillmentController::class, 'getTrackingTimeline'])->name('admin.orders.tracking');
    Route::post('courier-assignments/{assignment}/update-tracking', [OrderFulfillmentController::class, 'updateTracking'])->name('admin.courier-assignments.update-tracking');
    Route::get('couriers/list', [OrderFulfillmentController::class, 'couriers'])->name('admin.couriers.list');

    // Coupons
    Route::resource('coupons', CouponController::class)->names('admin.coupons');
    Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('admin.coupons.toggle-status');

    // Campaigns
    Route::resource('campaigns', CampaignController::class)->names('admin.campaigns');
    Route::get('campaigns/{campaign}/products', [CampaignController::class, 'manageProducts'])->name('admin.campaigns.products');
    Route::post('campaigns/{campaign}/products', [CampaignController::class, 'updateProducts'])->name('admin.campaigns.products.update');
    Route::post('campaigns/{campaign}/toggle-status', [CampaignController::class, 'toggleStatus'])->name('admin.campaigns.toggle-status');

    // Couriers
    Route::resource('couriers', CourierController::class)->names('admin.couriers');
    Route::post('couriers/{courier}/toggle-status', [CourierController::class, 'toggleStatus'])->name('admin.couriers.toggle-status');

    // Stock In (Bulk Only)
    Route::get('/stock-in', function () {
        return redirect()->route('admin.stock-in.bulk');
    })->name('admin.stock-in.index');
    Route::get('/stock-in/bulk', [StockInController::class, 'bulkCreate'])->name('admin.stock-in.bulk');
    Route::post('/stock-in/bulk', [StockInController::class, 'bulkStore'])->name('admin.stock-in.bulk.store');
    Route::get('/stock-in/products/{product}/variants', [StockInController::class, 'getVariants'])->name('admin.stock-in.variants');

    // Inventory Management - Enhanced
    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::get('/inventory/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('admin.inventory.low-stock');
    Route::get('/inventory/valuation', [InventoryController::class, 'valuation'])->name('admin.inventory.valuation');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('admin.inventory.movements');
    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('admin.inventory.edit');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('admin.inventory.update');
    Route::post('/inventory/{variant}/adjust', [InventoryController::class, 'adjustStock'])->name('admin.inventory.adjust');
    Route::get('/inventory/variants/{variant}/history', [InventoryController::class, 'history'])->name('admin.inventory.history');

    // Bulk Operations
    Route::prefix('bulk-operations')->group(function () {
        Route::get('/stock', [BulkOperationsController::class, 'bulkStock'])->name('admin.bulk.stock');
        Route::post('/stock', [BulkOperationsController::class, 'processBulkStock'])->name('admin.bulk.stock.process');
        Route::get('/price', [BulkOperationsController::class, 'bulkPrice'])->name('admin.bulk.price');
        Route::post('/price', [BulkOperationsController::class, 'processBulkPrice'])->name('admin.bulk.price.process');
        Route::post('/toggle-status', [BulkOperationsController::class, 'bulkToggleStatus'])->name('admin.bulk.toggle-status');
        Route::post('/delete', [BulkOperationsController::class, 'bulkDelete'])->name('admin.bulk.delete');
        Route::get('/export-variants', [BulkOperationsController::class, 'exportVariants'])->name('admin.bulk.export-variants');
        Route::post('/import-variants', [BulkOperationsController::class, 'importVariants'])->name('admin.bulk.import-variants');
    });

    // Users
    Route::resource('users', UserController::class)->names('admin.users');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->names('admin.roles');
    Route::resource('permissions', PermissionController::class)->names('admin.permissions');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');
    Route::get('activity-logs/user/{userId}', [ActivityLogController::class, 'userLogs'])->name('admin.activity-logs.user');
    Route::get('activity-logs/entity/{entityType}/{entityId?}', [ActivityLogController::class, 'entityLogs'])->name('admin.activity-logs.entity');

    // Notifications
    Route::resource('notifications', NotificationController::class)->names('admin.notifications');
    Route::get('notification-logs', [NotificationController::class, 'logs'])->name('admin.notifications.logs');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('settings/general', [SettingController::class, 'general'])->name('admin.settings.general');
    Route::get('settings/store', [SettingController::class, 'store'])->name('admin.settings.store');
    Route::get('settings/email', [SettingController::class, 'email'])->name('admin.settings.email');
    Route::get('settings/sms', [SettingController::class, 'sms'])->name('admin.settings.sms');
    Route::post('settings/sms/test', [SettingController::class, 'testSms'])->name('admin.settings.sms.test');
    Route::get('settings/payment', [SettingController::class, 'payment'])->name('admin.settings.payment');
    Route::get('settings/shipping', [SettingController::class, 'shipping'])->name('admin.settings.shipping');
    Route::get('settings/seo', [SettingController::class, 'seo'])->name('admin.settings.seo');
    Route::get('settings/social', [SettingController::class, 'social'])->name('admin.settings.social');
    Route::get('settings/footer', [SettingController::class, 'footer'])->name('admin.settings.footer');
    Route::post('settings/logo', [SettingController::class, 'uploadLogo'])->name('admin.settings.logo');
    Route::post('settings/favicon', [SettingController::class, 'uploadFavicon'])->name('admin.settings.favicon');
    Route::post('settings/clear-cache', [SettingController::class, 'clearCache'])->name('admin.settings.clear-cache');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('admin.reports.products');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('admin.reports.customers');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('admin.reports.inventory');
    Route::get('/reports/profit', [ReportController::class, 'profit'])->name('admin.reports.profit');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('admin.reports.expenses');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('admin.expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('admin.expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('admin.expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('admin.expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('admin.expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('admin.expenses.destroy');
    Route::get('/expenses/categories', [ExpenseController::class, 'categories'])->name('admin.expenses.categories');
    Route::post('/expenses/categories', [ExpenseController::class, 'storeCategory'])->name('admin.expenses.categories.store');
    Route::put('/expenses/categories/{category}', [ExpenseController::class, 'updateCategory'])->name('admin.expenses.categories.update');
    Route::delete('/expenses/categories/{category}', [ExpenseController::class, 'destroyCategory'])->name('admin.expenses.categories.destroy');

    // Sliders / Banners
    Route::resource('sliders', SliderController::class)->names('admin.sliders');
    Route::post('sliders/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('admin.sliders.toggle-status');
    Route::post('sliders/reorder', [SliderController::class, 'reorder'])->name('admin.sliders.reorder');

    // Homepage Management
    Route::resource('testimonials', TestimonialController::class)->names('admin.testimonials');
    Route::post('testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('admin.testimonials.toggle-status');
    
    Route::resource('brand-values', BrandValueController::class)->names('admin.brand-values');
    Route::post('brand-values/{brand_value}/toggle-status', [BrandValueController::class, 'toggleStatus'])->name('admin.brand-values.toggle-status');

    // POS System
    Route::prefix('pos')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('admin.pos.index');
        
        // Product Search
        Route::get('/products/search', [PosController::class, 'searchProducts'])->name('admin.pos.products.search');
        Route::get('/products/barcode/{barcode}', [PosController::class, 'findByBarcode'])->name('admin.pos.products.barcode');
        Route::get('/customers/search', [PosController::class, 'searchCustomers'])->name('admin.pos.customers.search');
        
        // Order Processing
        Route::post('/order', [PosController::class, 'createOrder'])->name('admin.pos.order.store');
        Route::get('/order/{id}', [PosController::class, 'showOrder'])->name('admin.pos.order.show');
        Route::get('/order/{id}/receipt', [PosController::class, 'getReceipt'])->name('admin.pos.order.receipt');
        Route::get('/order/{id}/print', [PosController::class, 'printReceipt'])->name('admin.pos.order.print');
        Route::post('/order/{id}/delivery-status', [PosController::class, 'updateDeliveryStatus'])->name('admin.pos.order.delivery-status');
        Route::get('/order/{id}/tracking', [PosController::class, 'getTrackingTimeline'])->name('admin.pos.order.tracking');
        
        // Reports
        Route::get('/reports/daily', [PosController::class, 'dailyReport'])->name('admin.pos.reports.daily');
    });
});
