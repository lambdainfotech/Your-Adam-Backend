<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
}
