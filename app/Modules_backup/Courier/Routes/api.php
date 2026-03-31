<?php

declare(strict_types=1);

use App\Modules\Courier\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/tracking', [TrackingController::class, 'track']); // Public

Route::middleware('auth:api')->group(function () {
    Route::get('/v1/orders/{id}/tracking', [TrackingController::class, 'show']);
});
