<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories;

use App\Modules\Notification\Models\EmailLog;
use App\Repositories\BaseRepository;

class EmailLogRepository extends BaseRepository
{
    public function __construct(EmailLog $model)
    {
        parent::__construct($model);
    }
}
