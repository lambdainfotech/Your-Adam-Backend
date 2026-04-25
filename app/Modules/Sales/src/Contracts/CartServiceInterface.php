<?php

declare(strict_types=1);

namespace App\Modules\Sales\Contracts;

use App\Modules\Sales\DTOs\AddToCartDTO;
use App\Modules\Sales\DTOs\ApplyCouponDTO;
use App\Modules\Sales\DTOs\CartSummaryDTO;
use App\Modules\Sales\DTOs\UpdateCartDTO;
use App\Modules\Sales\Models\Cart;
use App\Modules\Sales\Models\CartItem;

interface CartServiceInterface
{
    /**
     * Get or create cart for user.
     */
    public function getCart(int $userId): Cart;

    /**
     * Add item to cart.
     */
    public function addItem(int $userId, AddToCartDTO $dto): CartItem;

    /**
     * Update cart item quantity.
     */
    public function updateItem(int $userId, int $itemId, UpdateCartDTO $dto): CartItem;

    /**
     * Remove item from cart.
     */
    public function removeItem(int $userId, int $itemId): void;

    /**
     * Apply coupon to cart.
     */
    public function applyCoupon(int $userId, ApplyCouponDTO $dto): Cart;

    /**
     * Remove coupon from cart.
     */
    public function removeCoupon(int $userId): void;

    /**
     * Clear all items from cart.
     */
    public function clearCart(int $userId): void;

    /**
     * Get cart summary.
     */
    public function getCartSummary(int $userId): CartSummaryDTO;
}
