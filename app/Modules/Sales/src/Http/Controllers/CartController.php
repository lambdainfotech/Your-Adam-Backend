<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Contracts\CartServiceInterface;
use App\Modules\Sales\DTOs\AddToCartDTO;
use App\Modules\Sales\DTOs\ApplyCouponDTO;
use App\Modules\Sales\DTOs\UpdateCartDTO;
use App\Modules\Sales\Http\Requests\AddToCartRequest;
use App\Modules\Sales\Http\Requests\ApplyCouponRequest;
use App\Modules\Sales\Http\Requests\UpdateCartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->service->getCart($request->user()->id);

        return $this->successResponse([
            'cart' => $cart,
            'summary' => $this->service->getCartSummary($request->user()->id),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $item = $this->service->addItem(
            $request->user()->id,
            AddToCartDTO::fromRequest($request->validated())
        );

        return $this->createdResponse($item);
    }

    public function update(UpdateCartRequest $request, int $id): JsonResponse
    {
        $item = $this->service->updateItem(
            $request->user()->id,
            $id,
            UpdateCartDTO::fromRequest($request->validated())
        );

        return $this->successResponse($item);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->removeItem($request->user()->id, $id);

        return $this->noContentResponse();
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;

        // Guest checkout: accept subtotal for discount calculation
        if ($userId === null) {
            $couponCode = $request->input('coupon_code');
            $subtotal = (float) $request->input('subtotal', 0);

            $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))
                ->where('is_active', true)
                ->first();

            if (!$coupon) {
                return $this->errorResponse('Invalid coupon code.', 422);
            }

            if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
                return $this->errorResponse('Coupon has expired.', 422);
            }

            if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
                return $this->errorResponse('Coupon is not active yet.', 422);
            }

            if ($coupon->min_purchase_amount && $subtotal < $coupon->min_purchase_amount) {
                return $this->errorResponse(
                    'Minimum purchase amount of ৳' . $coupon->min_purchase_amount . ' required.',
                    422
                );
            }

            $discount = $coupon->calculateDiscount($subtotal);

            return $this->successResponse([
                'coupon' => [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'discountAmount' => $discount,
                    'subtotal' => $subtotal,
                    'finalAmount' => max(0, $subtotal - $discount),
                ],
            ]);
        }

        // Authenticated user
        $cart = $this->service->applyCoupon(
            $userId,
            ApplyCouponDTO::fromRequest($request->validated())
        );

        return $this->successResponse([
            'cart' => $cart,
            'summary' => $this->service->getCartSummary($userId),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $this->service->removeCoupon($request->user()->id);

        return $this->noContentResponse();
    }

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->service->getCartSummary($request->user()->id);

        return $this->successResponse($summary);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->service->clearCart($request->user()->id);

        return $this->noContentResponse();
    }
}
