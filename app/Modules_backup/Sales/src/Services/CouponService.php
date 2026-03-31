<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Contracts\CouponServiceInterface;
use App\Modules\Sales\Exceptions\InvalidCouponException;
use App\Modules\Sales\Models\Coupon;
use App\Modules\Sales\Repositories\CouponRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;

class CouponService implements CouponServiceInterface
{
    public function __construct(
        protected CouponRepository $repository,
        protected DatabaseManager $db,
    ) {}

    public function validate(string $code, int $userId, float $subtotal): Coupon
    {
        $coupon = $this->repository->findByCode($code);

        if ($coupon === null) {
            throw new InvalidCouponException('Coupon code not found.');
        }

        if (! $coupon->isValid()) {
            throw new InvalidCouponException('This coupon is invalid or has expired.');
        }

        if (! $coupon->canBeUsedBy($userId)) {
            throw new InvalidCouponException('You have reached the usage limit for this coupon.');
        }

        if ($coupon->min_purchase_amount !== null && $subtotal < $coupon->min_purchase_amount) {
            throw new InvalidCouponException(
                "Minimum purchase amount of {$coupon->min_purchase_amount} required."
            );
        }

        return $coupon;
    }

    public function apply(string $code, int $userId, float $subtotal): float
    {
        $coupon = $this->validate($code, $userId, $subtotal);

        return $coupon->calculateDiscount($subtotal);
    }

    public function getAvailableCoupons(int $userId, float $subtotal = 0): LengthAwarePaginator
    {
        $coupons = $this->repository->getActive();

        // Filter coupons that can be used by this user
        return $coupons->through(function (Coupon $coupon) use ($userId, $subtotal) {
            $coupon->can_use = $coupon->canBeUsedBy($userId) &&
                ($subtotal === 0 || $coupon->min_purchase_amount === null || $subtotal >= $coupon->min_purchase_amount);

            return $coupon;
        });
    }

    public function getByCode(string $code): ?Coupon
    {
        return $this->repository->findByCode($code);
    }

    public function recordUsage(int $couponId, int $userId, int $orderId, float $discountAmount): void
    {
        $this->db->transaction(function () use ($couponId, $userId, $orderId, $discountAmount) {
            $this->repository->recordUsage($couponId, $userId, $orderId, $discountAmount);
        });
    }
}
