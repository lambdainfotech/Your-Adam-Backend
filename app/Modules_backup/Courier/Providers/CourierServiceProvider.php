<?php

declare(strict_types=1);

namespace App\Modules\Courier\Providers;

use App\Modules\Courier\Contracts\CourierServiceInterface;
use App\Modules\Courier\Services\CourierService;
use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CourierServiceInterface::class, CourierService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
