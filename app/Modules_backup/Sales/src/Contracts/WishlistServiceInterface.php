<?php

declare(strict_types=1);

namespace App\Modules\Sales\Contracts;

use App\Modules\Sales\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WishlistServiceInterface
{
    /**
     * Get user's wishlist.
     */
    public function get(int $userId): LengthAwarePaginator;

    /**
     * Add product to wishlist.
     */
    public function add(int $userId, int $productId): Wishlist;

    /**
     * Remove product from wishlist.
     */
    public function remove(int $userId, int $productId): void;

    /**
     * Check if product is in wishlist.
     */
    public function isInWishlist(int $userId, int $productId): bool;

    /**
     * Toggle product in wishlist.
     */
    public function toggle(int $userId, int $productId): bool;
}
