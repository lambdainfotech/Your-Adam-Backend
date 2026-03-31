<?php

declare(strict_types=1);

namespace App\Modules\Courier\Repositories;

use App\Modules\Courier\Models\Courier;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class CourierRepository extends BaseRepository
{
    public function __construct(Courier $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
