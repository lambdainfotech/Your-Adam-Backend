<?php

declare(strict_types=1);

namespace App\Modules\Sales\Contracts;

use App\Modules\Sales\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CouponServiceInterface
{
    /**
     * Validate coupon code.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(string $code, int $userId, float $subtotal): Coupon;

    /**
     * Apply coupon and calculate discount.
     */
    public function apply(string $code, int $userId, float $subtotal): float;

    /**
     * Get available coupons for user.
     */
    public function getAvailableCoupons(int $userId, float $subtotal = 0): LengthAwarePaginator;

    /**
     * Get coupon by code.
     */
    public function getByCode(string $code): ?Coupon;

    /**
     * Record coupon usage.
     */
    public function recordUsage(int $couponId, int $userId, int $orderId, float $discountAmount): void;
}
