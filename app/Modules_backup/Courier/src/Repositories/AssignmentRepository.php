<?php

declare(strict_types=1);

namespace App\Modules\Courier\Repositories;

use App\Modules\Courier\Models\CourierAssignment;
use App\Repositories\BaseRepository;

class AssignmentRepository extends BaseRepository
{
    public function __construct(CourierAssignment $model)
    {
        parent::__construct($model);
    }
}
