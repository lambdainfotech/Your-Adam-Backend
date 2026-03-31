<?php

declare(strict_types=1);

namespace App\Modules\Audit\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AuditServiceInterface
{
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void;

    public function getActivityLogs(string $entityType, ?int $entityId = null, int $limit = 50): Collection;

    public function getUserActivity(int $userId, int $limit = 50): Collection;
}
