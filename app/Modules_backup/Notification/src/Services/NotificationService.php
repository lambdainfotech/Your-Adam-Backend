<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\NotificationServiceInterface;
use App\Modules\Notification\Repositories\NotificationRepository;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepository $repository
    ) {
    }

    public function sendToUser(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?array $data = null
    ): void {
        $this->repository->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    public function sendToUsers(
        array $userIds,
        string $type,
        string $title,
        string $body,
        ?array $data = null
    ): void {
        foreach ($userIds as $userId) {
            $this->sendToUser($userId, $type, $title, $body, $data);
        }
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        $notification = $this->repository->findForUser($notificationId, $userId);

        if ($notification) {
            $this->repository->update($notification, [
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->repository->getUnreadCount($userId);
    }

    public function getForUser(int $userId): array
    {
        return $this->repository->getForUser($userId);
    }
}
