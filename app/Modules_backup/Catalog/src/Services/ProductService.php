<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Contracts\ProductServiceInterface;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Repositories\ProductRepository;
use App\Modules\Shared\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Cache\CacheManager;

class ProductService extends BaseService implements ProductServiceInterface
{
    public function __construct(
        private ProductRepository $repository,
        private CacheManager $cache
    ) {
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $cacheKey = 'products:list:' . md5(json_encode($filters));
        
        return $this->cache->remember(
            $cacheKey,
            1800,
            fn() => $this->repository->list($filters)
        );
    }

    public function getBySlug(string $slug): Product
    {
        return $this->cache->remember(
            "product:{$slug}",
            3600,
            fn() => $this->repository->findBySlug($slug)
        );
    }

    public function search(string $query, array $filters): LengthAwarePaginator
    {
        $cacheKey = 'products:search:' . md5($query . json_encode($filters));
        
        return $this->cache->remember(
            $cacheKey,
            1800,
            fn() => $this->repository->search($query, $filters)
        );
    }
}
