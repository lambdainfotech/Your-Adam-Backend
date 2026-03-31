<?php

declare(strict_types=1);

namespace App\Modules\Courier\Repositories;

use App\Modules\Courier\Models\TrackingHistory;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class TrackingRepository extends BaseRepository
{
    public function __construct(TrackingHistory $model)
    {
        parent::__construct($model);
    }

    public function getByOrderId(int $orderId): Collection
    {
        return $this->model
            ->where('order_id', $orderId)
            ->orderBy('tracked_at', 'desc')
            ->get();
    }
}
