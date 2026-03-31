<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Repositories;

use App\Modules\Catalog\Models\Product;
use App\Modules\Shared\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'products';
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model
            ->with(['category', 'images', 'variants.attributeValues', 'attributes.values'])
            ->where('slug', $slug)
            ->first();
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('mainImage')->active();

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('base_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['search'])) {
            $query->whereFullText(['name', 'short_description'], $filters['search']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function search(string $query, array $filters): LengthAwarePaginator
    {
        $queryBuilder = $this->model->with('mainImage')->active();

        $queryBuilder->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%")
              ->orWhere('sku_prefix', 'LIKE', "%{$query}%");
        });

        if (!empty($filters['category_id'])) {
            $queryBuilder->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $queryBuilder->where('base_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $queryBuilder->where('base_price', '<=', $filters['max_price']);
        }

        return $queryBuilder->paginate($filters['per_page'] ?? 20);
    }
}
