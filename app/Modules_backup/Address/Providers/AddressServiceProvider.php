<?php

declare(strict_types=1);

namespace App\Modules\Address\Providers;

use App\Modules\Address\Contracts\AddressRepositoryInterface;
use App\Modules\Address\Repositories\AddressRepository;
use Illuminate\Support\ServiceProvider;

class AddressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
