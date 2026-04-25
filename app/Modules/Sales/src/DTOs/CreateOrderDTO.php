<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTOs;

use App\Modules\Sales\Enums\PaymentMethod;

class CreateOrderDTO
{
    public function __construct(
        public readonly int $addressId,
        public readonly PaymentMethod $paymentMethod,
        public readonly ?string $couponCode = null,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            addressId: (int) $data['address_id'],
            paymentMethod: PaymentMethod::from($data['payment_method']),
            couponCode: $data['coupon_code'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            addressId: $data['address_id'],
            paymentMethod: $data['payment_method'],
            couponCode: $data['coupon_code'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'address_id' => $this->addressId,
            'payment_method' => $this->paymentMethod->value,
            'coupon_code' => $this->couponCode,
            'notes' => $this->notes,
        ];
    }
}
