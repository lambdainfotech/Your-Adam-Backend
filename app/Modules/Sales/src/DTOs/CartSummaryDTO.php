<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTOs;

class CartSummaryDTO
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $discount,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total,
        public readonly int $itemsCount,
        public readonly ?string $couponCode = null,
        public readonly ?float $couponDiscount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subtotal: $data['subtotal'] ?? 0,
            discount: $data['discount'] ?? 0,
            tax: $data['tax'] ?? 0,
            shipping: $data['shipping'] ?? 0,
            total: $data['total'] ?? 0,
            itemsCount: $data['items_count'] ?? 0,
            couponCode: $data['coupon_code'] ?? null,
            couponDiscount: $data['coupon_discount'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'shipping' => $this->shipping,
            'total' => $this->total,
            'items_count' => $this->itemsCount,
            'coupon_code' => $this->couponCode,
            'coupon_discount' => $this->couponDiscount,
        ];
    }
}
