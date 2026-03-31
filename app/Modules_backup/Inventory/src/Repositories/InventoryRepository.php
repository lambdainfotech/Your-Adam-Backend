<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Repositories;

use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Support\Collection;

class InventoryRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return InventoryMovement::class;
    }

    protected function getCachePrefix(): string
    {
        return 'inventory';
    }

    public function logMovement(array $data): InventoryMovement
    {
        return $this->create($data);
    }

    public function getMovementsByVariant(int $variantId, int $limit = 50): Collection
    {
        return $this->findByCriteria(
            ['variant_id' => $variantId],
            ['created_at' => 'desc']
        )->take($limit);
    }
}
