<?php

use App\Modules\Report\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/admin', 'middleware' => ['auth:api']], function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::get('/reports/customers', [ReportController::class, 'customers']);
    Route::get('/reports/coupons', [ReportController::class, 'coupons']);
    Route::post('/reports/export', [ReportController::class, 'export']);
});
