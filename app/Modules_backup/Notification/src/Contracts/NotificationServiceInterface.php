<?php

declare(strict_types=1);

namespace App\Modules\Notification\Contracts;

interface NotificationServiceInterface
{
    public function sendToUser(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?array $data = null
    ): void;

    public function sendToUsers(
        array $userIds,
        string $type,
        string $title,
        string $body,
        ?array $data = null
    ): void;

    public function markAsRead(int $notificationId, int $userId): void;

    public function getUnreadCount(int $userId): int;

    public function getForUser(int $userId): array;
}
