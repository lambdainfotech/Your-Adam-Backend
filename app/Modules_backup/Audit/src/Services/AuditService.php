<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Contracts\AuditServiceInterface;
use App\Modules\Audit\Repositories\ActivityLogRepository;
use Illuminate\Database\Eloquent\Collection;

class AuditService implements AuditServiceInterface
{
    public function __construct(
        private ActivityLogRepository $repository
    ) {}

    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        $this->repository->create([
            'user_id' => auth()->id(),
            'user_type' => auth()->check() ? (auth()->user()->isAdmin() ? 'admin' : 'customer') : 'system',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getActivityLogs(string $entityType, ?int $entityId = null, int $limit = 50): Collection
    {
        return $this->repository->query()
            ->forEntity($entityType, $entityId)
            ->with('user:id,mobile')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getUserActivity(int $userId, int $limit = 50): Collection
    {
        return $this->repository->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
