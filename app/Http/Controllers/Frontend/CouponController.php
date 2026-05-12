<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    use ApiResponse;
    private CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Validate coupon code
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        $result = $this->couponService->validateCoupon(
            $request->get('code'),
            (float) $request->get('subtotal'),
            $userId
        );

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['coupon'], 'Coupon validated successfully');
    }

    /**
     * Get available coupons
     */
    public function available(): JsonResponse
    {
        $userId = Auth::id();
        $coupons = $this->couponService->getAvailableCoupons($userId);

        return $this->success($coupons, 'Available coupons retrieved successfully');
    }

    /**
     * Get coupon details by code
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return $this->error('Coupon not found', 404);
        }

        return $this->success([
            'id' => $coupon->id,
            'code' => $coupon->code,
            'description' => $coupon->description,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'minPurchaseAmount' => $coupon->min_purchase_amount,
            'maxDiscountAmount' => $coupon->max_discount_amount,
            'usageLimitPerUser' => $coupon->usage_limit_per_user,
            'totalUsageLimit' => $coupon->total_usage_limit,
            'usageCount' => $coupon->usage_count,
            'startsAt' => $coupon->starts_at?->toIso8601String(),
            'expiresAt' => $coupon->expires_at?->toIso8601String(),
            'isActive' => $coupon->is_active,
            'isValid' => $coupon->is_valid,
            'isExpired' => $coupon->is_expired,
        ], 'Coupon details retrieved successfully');
    }
}
