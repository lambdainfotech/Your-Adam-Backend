<?php

declare(strict_types=1);

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Contracts\NotificationServiceInterface;
use App\Modules\Notification\Contracts\EmailServiceInterface;
use App\Modules\Notification\Contracts\SMSServiceInterface;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Services\EmailService;
use App\Modules\Notification\Services\SMSService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(EmailServiceInterface::class, EmailService::class);
        $this->app->bind(SMSServiceInterface::class, SMSService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
