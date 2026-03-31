<?php

declare(strict_types=1);

namespace App\Modules\User\Providers;

use App\Modules\User\Contracts\AddressServiceInterface;
use App\Modules\User\Contracts\ProfileServiceInterface;
use App\Modules\User\Services\AddressService;
use App\Modules\User\Services\ProfileService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(AddressServiceInterface::class, AddressService::class);
    }

    public function boot(): void
    {
        //
    }
}
