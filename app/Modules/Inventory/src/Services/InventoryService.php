<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Contracts\InventoryServiceInterface;
use App\Modules\Inventory\Enums\MovementType;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Repositories\InventoryRepository;
use App\Modules\Product\Repositories\VariantRepository;
use App\Modules\Shared\Services\BaseService;
use Illuminate\Support\Collection;

class InventoryService extends BaseService implements InventoryServiceInterface
{
    public function __construct(
        private InventoryRepository $repository,
        private VariantRepository $variantRepository
    ) {
    }

    public function getStock(int $variantId): int
    {
        $variant = $this->variantRepository->find($variantId);

        return $variant ? $variant->stock_quantity : 0;
    }

    public function decreaseStock(
        int $variantId,
        int $quantity,
        string $reason,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void {
        $this->transaction(function () use ($variantId, $quantity, $reason, $referenceId, $referenceType) {
            $variant = $this->variantRepository->find($variantId);
            $previousStock = $variant->stock_quantity;

            if ($previousStock < $quantity) {
                throw new InsufficientStockException("Only {$previousStock} items available");
            }

            $newStock = $previousStock - $quantity;
            $this->variantRepository->update($variantId, ['stock_quantity' => $newStock]);

            $this->repository->logMovement([
                'variant_id' => $variantId,
                'type' => MovementType::OUT,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);

            if ($newStock <= $variant->low_stock_alert) {
                LowStockAlert::dispatch($variant);
            }
        });
    }

    public function increaseStock(
        int $variantId,
        int $quantity,
        string $reason,
        ?int $referenceId = null,
        ?string $referenceType = null
    ): void {
        $this->transaction(function () use ($variantId, $quantity, $reason, $referenceId, $referenceType) {
            $variant = $this->variantRepository->find($variantId);
            $previousStock = $variant->stock_quantity;
            $newStock = $previousStock + $quantity;

            $this->variantRepository->update($variantId, ['stock_quantity' => $newStock]);

            $this->repository->logMovement([
                'variant_id' => $variantId,
                'type' => MovementType::IN,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function adjustStock(int $variantId, int $newQuantity, string $reason): void
    {
        $this->transaction(function () use ($variantId, $newQuantity, $reason) {
            $variant = $this->variantRepository->find($variantId);
            $previousStock = $variant->stock_quantity;
            $quantity = abs($newQuantity - $previousStock);

            $this->variantRepository->update($variantId, ['stock_quantity' => $newQuantity]);

            $this->repository->logMovement([
                'variant_id' => $variantId,
                'type' => MovementType::ADJUSTMENT,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newQuantity,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);

            if ($newQuantity <= $variant->low_stock_alert) {
                LowStockAlert::dispatch($variant);
            }
        });
    }

    public function getLowStockVariants(): Collection
    {
        return $this->variantRepository->getLowStockVariants();
    }

    public function getMovementHistory(int $variantId, int $limit = 50): Collection
    {
        return $this->repository->getMovementsByVariant($variantId, $limit);
    }
}
