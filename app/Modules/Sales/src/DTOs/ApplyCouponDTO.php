<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTOs;

class ApplyCouponDTO
{
    public function __construct(
        public readonly string $couponCode,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            couponCode: $data['coupon_code'],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            couponCode: $data['coupon_code'],
        );
    }

    public function toArray(): array
    {
        return [
            'coupon_code' => $this->couponCode,
        ];
    }
}
