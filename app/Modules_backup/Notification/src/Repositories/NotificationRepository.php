<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories;

use App\Modules\Notification\Models\Notification;
use App\Repositories\BaseRepository;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function getForUser(int $userId): array
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->toArray();
    }

    public function findForUser(int $notificationId, int $userId): ?Notification
    {
        return $this->model
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();
    }
}
