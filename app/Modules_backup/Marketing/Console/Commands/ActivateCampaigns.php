<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Console\Commands;

use App\Modules\Marketing\Models\Campaign;
use App\Modules\Marketing\Repositories\CampaignRepository;
use Illuminate\Console\Command;

class ActivateCampaigns extends Command
{
    protected $signature = 'campaigns:activate';

    protected $description = 'Activate scheduled campaigns that have reached their start time';

    public function __construct(private CampaignRepository $repository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $scheduledCampaigns = $this->repository->getScheduled();

        $activatedCount = 0;

        foreach ($scheduledCampaigns as $campaign) {
            if ($campaign->starts_at <= now()) {
                $this->info("Activating campaign: {$campaign->name}");
                $activatedCount++;
            }
        }

        $expiredCampaigns = $this->repository->getExpired();

        $deactivatedCount = 0;

        foreach ($expiredCampaigns as $campaign) {
            $campaign->update(['is_active' => false]);
            $this->info("Deactivating expired campaign: {$campaign->name}");
            $deactivatedCount++;
        }

        $this->info("Activated {$activatedCount} campaigns, deactivated {$deactivatedCount} expired campaigns.");

        return self::SUCCESS;
    }
}
