<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Contracts;

use App\Modules\Catalog\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface VariantRepositoryInterface
{
    public function find(int $id): ?Variant;

    public function update(int $id, array $data): bool;

    public function query(): Builder;

    public function getLowStockVariants(int $threshold = 10): Collection;

    public function decrementStock(int $variantId, int $quantity): bool;

    public function incrementStock(int $variantId, int $quantity): bool;
}
