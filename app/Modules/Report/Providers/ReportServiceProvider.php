<?php

namespace App\Modules\Report\Providers;

use App\Modules\Report\Contracts\ReportServiceInterface;
use App\Modules\Report\Services\ReportService;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReportServiceInterface::class, ReportService::class);
    }

    public function boot(): void
    {
        //
    }
}
