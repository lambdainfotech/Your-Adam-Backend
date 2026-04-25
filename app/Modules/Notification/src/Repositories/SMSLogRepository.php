<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories;

use App\Modules\Notification\Models\SMSLog;
use App\Repositories\BaseRepository;

class SMSLogRepository extends BaseRepository
{
    public function __construct(SMSLog $model)
    {
        parent::__construct($model);
    }
}
