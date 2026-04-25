<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository
{
    public function __construct(
        protected Wishlist $model,
    ) {}

    public function find(int $id): ?Wishlist
    {
        return $this->model
            ->with('product')
            ->find($id);
    }

    public function findByUserAndProduct(int $userId, int $productId): ?Wishlist
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['product' => function ($query) {
                $query->with(['media', 'variants']);
            }])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllByUser(int $userId): Collection
    {
        return $this->model
            ->with('product')
            ->where('user_id', $userId)
            ->get();
    }

    public function create(array $data): Wishlist
    {
        return $this->model->create($data);
    }

    public function delete(int $id): bool
    {
        return $this->model
            ->where('id', $id)
            ->delete();
    }

    public function deleteByUserAndProduct(int $userId, int $productId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function exists(int $userId, int $productId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
