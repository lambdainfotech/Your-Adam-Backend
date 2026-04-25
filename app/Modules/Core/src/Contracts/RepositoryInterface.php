<?php

declare(strict_types=1);

namespace App\Modules\Core\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Find a model by its primary key.
     */
    public function find(int|string $id): ?Model;

    /**
     * Find a model by specific criteria.
     */
    public function findBy(array $criteria, array $orderBy = null): ?Model;

    /**
     * Find all models.
     */
    public function findAll(array $orderBy = null): Collection;

    /**
     * Find models by criteria.
     */
    public function findByCriteria(array $criteria, array $orderBy = null): Collection;

    /**
     * Paginate results.
     */
    public function paginate(int $perPage = 15, array $criteria = [], array $orderBy = null): LengthAwarePaginator;

    /**
     * Create a new model.
     */
    public function create(array $data): Model;

    /**
     * Update a model.
     */
    public function update(int|string $id, array $data): Model;

    /**
     * Delete a model.
     */
    public function delete(int|string $id): bool;

    /**
     * Soft delete a model.
     */
    public function softDelete(int|string $id): bool;

    /**
     * Restore a soft-deleted model.
     */
    public function restore(int|string $id): bool;
}
