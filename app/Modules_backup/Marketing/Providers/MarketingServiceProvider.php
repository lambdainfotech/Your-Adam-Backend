<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Providers;

use App\Modules\Marketing\Console\Commands\ActivateCampaigns;
use App\Modules\Marketing\Contracts\CampaignServiceInterface;
use App\Modules\Marketing\Repositories\CampaignRepository;
use App\Modules\Marketing\Services\CampaignService;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ActivateCampaigns::class,
            ]);
        }
    }
}
