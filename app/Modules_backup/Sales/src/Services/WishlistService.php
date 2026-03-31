<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Contracts\WishlistServiceInterface;
use App\Modules\Sales\Models\Wishlist;
use App\Modules\Sales\Repositories\WishlistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishlistService implements WishlistServiceInterface
{
    public function __construct(
        protected WishlistRepository $repository,
    ) {}

    public function get(int $userId): LengthAwarePaginator
    {
        return $this->repository->getByUser($userId);
    }

    public function add(int $userId, int $productId): Wishlist
    {
        $existing = $this->repository->findByUserAndProduct($userId, $productId);

        if ($existing !== null) {
            return $existing;
        }

        return $this->repository->create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    public function remove(int $userId, int $productId): void
    {
        $this->repository->deleteByUserAndProduct($userId, $productId);
    }

    public function isInWishlist(int $userId, int $productId): bool
    {
        return $this->repository->exists($userId, $productId);
    }

    public function toggle(int $userId, int $productId): bool
    {
        if ($this->isInWishlist($userId, $productId)) {
            $this->remove($userId, $productId);

            return false;
        }

        $this->add($userId, $productId);

        return true;
    }
}
