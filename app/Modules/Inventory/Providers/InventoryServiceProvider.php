<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Contracts\InventoryServiceInterface;
use App\Modules\Inventory\Repositories\InventoryRepository;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryRepository::class, function ($app) {
            return new InventoryRepository();
        });

        $this->app->bind(InventoryServiceInterface::class, function ($app) {
            return new InventoryService(
                $app->make(InventoryRepository::class),
                $app->make(\App\Modules\Product\src\Repositories\VariantRepository::class)
            );
        });
    }

    public function boot(): void
    {
        // Module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Module routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
