<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Repositories;

use App\Modules\Core\Abstracts\BaseRepository;
use App\Modules\Marketing\Models\Campaign;
use Illuminate\Support\Collection;

class CampaignRepository extends BaseRepository
{
    public function __construct(Campaign $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'campaigns';
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();
    }

    public function getScheduled(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where('starts_at', '>', now())
            ->get();
    }

    public function getExpired(): Collection
    {
        return $this->query()
            ->where('ends_at', '<', now())
            ->where('is_active', true)
            ->get();
    }
}
