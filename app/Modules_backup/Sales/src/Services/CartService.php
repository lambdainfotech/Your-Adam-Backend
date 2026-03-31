<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Modules\Catalog\Contracts\VariantRepositoryInterface;
use App\Modules\Sales\Contracts\CartServiceInterface;
use App\Modules\Sales\Contracts\CouponServiceInterface;
use App\Modules\Sales\DTOs\AddToCartDTO;
use App\Modules\Sales\DTOs\ApplyCouponDTO;
use App\Modules\Sales\DTOs\CartSummaryDTO;
use App\Modules\Sales\DTOs\UpdateCartDTO;
use App\Modules\Sales\Exceptions\InsufficientStockException;
use App\Modules\Sales\Models\Cart;
use App\Modules\Sales\Models\CartItem;
use App\Modules\Sales\Repositories\CartRepository;
use Illuminate\Database\DatabaseManager;

class CartService implements CartServiceInterface
{
    public function __construct(
        protected CartRepository $repository,
        protected VariantRepositoryInterface $variantRepository,
        protected CouponServiceInterface $couponService,
        protected DatabaseManager $db,
    ) {}

    public function getCart(int $userId): Cart
    {
        return $this->repository->findOrCreateByUserId($userId);
    }

    public function addItem(int $userId, AddToCartDTO $dto): CartItem
    {
        return $this->transaction(function () use ($userId, $dto) {
            $cart = $this->getCart($userId);
            $variant = $this->variantRepository->find($dto->variantId);

            if ($variant === null) {
                throw new \InvalidArgumentException('Variant not found.');
            }

            if ($variant->stock_quantity < $dto->quantity) {
                throw new InsufficientStockException();
            }

            return $cart->items()->updateOrCreate(
                ['variant_id' => $dto->variantId],
                [
                    'quantity' => $dto->quantity,
                    'unit_price' => $variant->price,
                ]
            );
        });
    }

    public function updateItem(int $userId, int $itemId, UpdateCartDTO $dto): CartItem
    {
        return $this->transaction(function () use ($userId, $itemId, $dto) {
            $cart = $this->getCart($userId);
            $item = $cart->items()->findOrFail($itemId);
            $variant = $this->variantRepository->find($item->variant_id);

            if ($variant->stock_quantity < $dto->quantity) {
                throw new InsufficientStockException();
            }

            $item->update(['quantity' => $dto->quantity]);

            return $item->fresh();
        });
    }

    public function removeItem(int $userId, int $itemId): void
    {
        $cart = $this->getCart($userId);
        $cart->items()->where('id', $itemId)->delete();
    }

    public function applyCoupon(int $userId, ApplyCouponDTO $dto): Cart
    {
        return $this->transaction(function () use ($userId, $dto) {
            $cart = $this->getCart($userId);
            $subtotal = $cart->subtotal;

            $coupon = $this->couponService->validate($dto->couponCode, $userId, $subtotal);
            $discount = $coupon->calculateDiscount($subtotal);

            $cart->update([
                'coupon_code' => $coupon->code,
                'coupon_discount' => $discount,
            ]);

            return $cart->fresh();
        });
    }

    public function removeCoupon(int $userId): void
    {
        $cart = $this->getCart($userId);
        $cart->update([
            'coupon_code' => null,
            'coupon_discount' => null,
        ]);
    }

    public function clearCart(int $userId): void
    {
        $cart = $this->getCart($userId);
        $cart->items()->delete();
        $cart->update([
            'coupon_code' => null,
            'coupon_discount' => null,
        ]);
    }

    public function getCartSummary(int $userId): CartSummaryDTO
    {
        $cart = $this->getCart($userId);
        $subtotal = $cart->subtotal;
        $discount = $cart->coupon_discount ?? 0;
        $tax = $this->calculateTax($subtotal);
        $shipping = $this->calculateShipping($cart);
        $total = max(0, $subtotal - $discount + $tax + $shipping);

        return new CartSummaryDTO(
            subtotal: round($subtotal, 2),
            discount: round($discount, 2),
            tax: round($tax, 2),
            shipping: round($shipping, 2),
            total: round($total, 2),
            itemsCount: $cart->items_count,
            couponCode: $cart->coupon_code,
            couponDiscount: $cart->coupon_discount,
        );
    }

    protected function calculateTax(float $subtotal): float
    {
        $taxRate = config('sales.tax_rate', 0);

        return $subtotal * $taxRate;
    }

    protected function calculateShipping(Cart $cart): float
    {
        $baseShipping = config('sales.base_shipping', 0);
        $freeShippingThreshold = config('sales.free_shipping_threshold');

        if ($freeShippingThreshold !== null && $cart->subtotal >= $freeShippingThreshold) {
            return 0;
        }

        return $baseShipping;
    }

    protected function transaction(\Closure $callback)
    {
        return $this->db->transaction($callback);
    }
}
