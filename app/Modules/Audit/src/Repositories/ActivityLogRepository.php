<?php

declare(strict_types=1);

namespace App\Modules\Audit\Repositories;

use App\Modules\Audit\Models\ActivityLog;
use App\Modules\Core\Abstracts\BaseRepository;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'activity_logs';
    }
}
