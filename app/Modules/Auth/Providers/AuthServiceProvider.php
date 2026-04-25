<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Contracts\AuthServiceInterface;
use App\Modules\Auth\Contracts\JWTServiceInterface;
use App\Modules\Auth\Contracts\OTPServiceInterface;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\JWTService;
use App\Modules\Auth\Services\OTPService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OTPServiceInterface::class, OTPService::class);
        $this->app->bind(JWTServiceInterface::class, JWTService::class);
    }

    public function boot(): void
    {
        //
    }
}
