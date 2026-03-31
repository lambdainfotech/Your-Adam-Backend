<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Contracts;

use Illuminate\Support\Collection;

interface InventoryServiceInterface
{
    public function getStock(int $variantId): int;

    public function increaseStock(
        int $variantId,
        int $quantity,
        string $reason,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void;

    public function decreaseStock(
        int $variantId,
        int $quantity,
        string $reason,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void;

    public function adjustStock(int $variantId, int $newQuantity, string $reason): void;

    public function getLowStockVariants(): Collection;

    public function getMovementHistory(int $variantId, int $limit = 50): Collection;
}
