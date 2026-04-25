<?php

use App\Modules\User\Http\Controllers\AddressController;
use App\Modules\User\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    Route::get('/users/profile', [ProfileController::class, 'show']);
    Route::put('/users/profile', [ProfileController::class, 'update']);
    Route::apiResource('/users/addresses', AddressController::class);
    Route::patch('/users/addresses/{id}/default', [AddressController::class, 'setDefault']);
});
