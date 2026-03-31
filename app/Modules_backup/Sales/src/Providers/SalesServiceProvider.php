<?php

declare(strict_types=1);

namespace App\Modules\Sales\Providers;

use App\Modules\Sales\Contracts\CartServiceInterface;
use App\Modules\Sales\Contracts\CouponServiceInterface;
use App\Modules\Sales\Contracts\OrderServiceInterface;
use App\Modules\Sales\Contracts\WishlistServiceInterface;
use App\Modules\Sales\Repositories\CartRepository;
use App\Modules\Sales\Repositories\CouponRepository;
use App\Modules\Sales\Repositories\OrderRepository;
use App\Modules\Sales\Repositories\WishlistRepository;
use App\Modules\Sales\Services\CartService;
use App\Modules\Sales\Services\CouponService;
use App\Modules\Sales\Services\OrderService;
use App\Modules\Sales\Services\WishlistService;
use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Repositories
        $this->app->singleton(CartRepository::class, function ($app) {
            return new CartRepository(new \App\Modules\Sales\Models\Cart());
        });

        $this->app->singleton(OrderRepository::class, function ($app) {
            return new OrderRepository(new \App\Modules\Sales\Models\Order());
        });

        $this->app->singleton(CouponRepository::class, function ($app) {
            return new CouponRepository(
                new \App\Modules\Sales\Models\Coupon(),
                new \App\Modules\Sales\Models\CouponUsage()
            );
        });

        $this->app->singleton(WishlistRepository::class, function ($app) {
            return new WishlistRepository(new \App\Modules\Sales\Models\Wishlist());
        });

        // Register Services
        $this->app->singleton(CartServiceInterface::class, CartService::class);
        $this->app->singleton(OrderServiceInterface::class, OrderService::class);
        $this->app->singleton(CouponServiceInterface::class, CouponService::class);
        $this->app->singleton(WishlistServiceInterface::class, WishlistService::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/sales.php',
            'sales'
        );

        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
