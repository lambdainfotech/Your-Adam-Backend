<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;

class CouponService
{
    /**
     * Validate and calculate coupon discount
     */
    public function validateCoupon(string $code, float $subtotal, ?int $userId = null): array
    {
        $coupon = Coupon::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid coupon code',
            ];
        }

        // Check expiry
        if ($coupon->expires_at && Carbon::now()->gt($coupon->expires_at)) {
            return [
                'success' => false,
                'message' => 'Coupon has expired',
            ];
        }

        // Check start date
        if ($coupon->starts_at && Carbon::now()->lt($coupon->starts_at)) {
            return [
                'success' => false,
                'message' => 'Coupon is not active yet',
            ];
        }

        // Check minimum purchase
        if ($coupon->min_purchase_amount && $subtotal < $coupon->min_purchase_amount) {
            return [
                'success' => false,
                'message' => 'Minimum purchase amount of ৳' . $coupon->min_purchase_amount . ' required',
            ];
        }

        // Check total usage limit
        if ($coupon->total_usage_limit && $coupon->usage_count >= $coupon->total_usage_limit) {
            return [
                'success' => false,
                'message' => 'Coupon usage limit reached',
            ];
        }

        // Check per-user limit
        if ($userId && $coupon->usage_limit_per_user) {
            $userUsageCount = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->count();
            
            if ($userUsageCount >= $coupon->usage_limit_per_user) {
                return [
                    'success' => false,
                    'message' => 'You have already used this coupon',
                ];
            }
        }

        // Calculate discount
        $discount = $this->calculateDiscount($coupon, $subtotal);

        return [
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'discountAmount' => $discount,
                'subtotal' => $subtotal,
                'finalAmount' => $subtotal - $discount,
            ],
        ];
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = 0;

        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ($coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        // Apply max discount limit
        if ($coupon->max_discount_amount && $discount > $coupon->max_discount_amount) {
            $discount = $coupon->max_discount_amount;
        }

        // Ensure discount doesn't exceed subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return round($discount, 2);
    }

    /**
     * Apply coupon to order
     */
    public function applyCoupon(int $couponId, int $orderId, int $userId, float $discountAmount): void
    {
        CouponUsage::create([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        // Increment usage count
        Coupon::where('id', $couponId)->increment('usage_count');
    }

    /**
     * Get available coupons for user
     */
    public function getAvailableCoupons(?int $userId = null): array
    {
        $query = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('total_usage_limit')
                    ->orWhereRaw('usage_count < total_usage_limit');
            });

        return $query->get()->map(function ($coupon) use ($userId) {
            $canUse = true;
            $message = null;

            if ($userId && $coupon->usage_limit_per_user) {
                $userUsage = CouponUsage::where('coupon_id', $coupon->id)
                    ->where('user_id', $userId)
                    ->count();
                
                if ($userUsage >= $coupon->usage_limit_per_user) {
                    $canUse = false;
                    $message = 'Already used';
                }
            }

            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'minPurchase' => $coupon->min_purchase_amount,
                'maxDiscount' => $coupon->max_discount_amount,
                'expiresAt' => $coupon->expires_at?->format('M d, Y'),
                'canUse' => $canUse,
                'message' => $message,
            ];
        })->toArray();
    }
}
