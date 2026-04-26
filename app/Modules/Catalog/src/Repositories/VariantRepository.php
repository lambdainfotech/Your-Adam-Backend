<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Repositories;

use App\Modules\Catalog\Contracts\VariantRepositoryInterface;
use App\Modules\Catalog\Models\Variant;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VariantRepository extends BaseRepository implements VariantRepositoryInterface
{
    public function __construct(Variant $model)
    {
        parent::__construct($model);
    }

    public function find(int $id): ?Variant
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $variant = $this->model->find($id);
        
        if (!$variant) {
            return false;
        }

        return $variant->update($data);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function getLowStockVariants(int $threshold = 10): Collection
    {
        return $this->model->where('stock_quantity', '<=', $threshold)->get();
    }

    public function decrementStock(int $variantId, int $quantity): bool
    {
        $affected = $this->model->where('id', $variantId)
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity);

        if ($affected > 0) {
            $variant = $this->model->find($variantId);
            if ($variant) {
                $variant->updateStockStatus();
            }
        }

        return $affected > 0;
    }

    public function incrementStock(int $variantId, int $quantity): bool
    {
        $variant = $this->model->find($variantId);
        
        if (!$variant) {
            return false;
        }

        $variant->increment('stock_quantity', $quantity);
        $variant->updateStockStatus();
        
        return true;
    }
}
