<?php

declare(strict_types=1);

namespace App\Modules\Core\Abstracts;

use App\Modules\Core\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected ?string $cachePrefix = null;
    protected int $cacheTtl = 3600; // 1 hour

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->cachePrefix = $this->getCachePrefix();
    }

    protected function getCachePrefix(): string
    {
        return 'repo:' . class_basename($this->model);
    }

    protected function getCacheKey(string $key): string
    {
        return "{$this->cachePrefix}:{$key}";
    }

    protected function forgetCache(string $pattern): void
    {
        // Clear cache by pattern (requires Redis or cache tags)
        Cache::flush();
    }

    public function find(int|string $id): ?Model
    {
        $cacheKey = $this->getCacheKey("find:{$id}");
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->find($id);
        });
    }

    public function findBy(array $criteria, array $orderBy = null): ?Model
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->first();
    }

    public function findAll(array $orderBy = null): Collection
    {
        $query = $this->model->newQuery();

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->get();
    }

    public function findByCriteria(array $criteria, array $orderBy = null): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $criteria = [], array $orderBy = null): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Model
    {
        $model = $this->model->create($data);
        $this->forgetCache("*");
        
        return $model;
    }

    public function update(int|string $id, array $data): Model
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
        
        Cache::forget($this->getCacheKey("find:{$id}"));
        $this->forgetCache("*");
        
        return $model->fresh();
    }

    public function delete(int|string $id): bool
    {
        $model = $this->model->findOrFail($id);
        $result = $model->delete();
        
        Cache::forget($this->getCacheKey("find:{$id}"));
        $this->forgetCache("*");
        
        return $result;
    }

    public function softDelete(int|string $id): bool
    {
        return $this->delete($id);
    }

    public function restore(int|string $id): bool
    {
        $result = $this->model->withTrashed()->findOrFail($id)->restore();
        
        Cache::forget($this->getCacheKey("find:{$id}"));
        $this->forgetCache("*");
        
        return $result;
    }

    /**
     * Get model instance for query building.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Create a new query builder.
     */
    public function query()
    {
        return $this->model->newQuery();
    }
}
