<?php

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Contracts\AuditServiceInterface;
use App\Modules\Audit\Listeners\ActivityLogListener;
use App\Modules\Audit\Services\AuditService;
use App\Modules\Sales\Events\OrderCreated;
use App\Modules\Sales\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditServiceInterface::class, AuditService::class);
    }

    public function boot(): void
    {
        Event::listen(OrderCreated::class, ActivityLogListener::class);
        Event::listen(OrderStatusChanged::class, ActivityLogListener::class);
    }
}
