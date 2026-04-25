<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTOs;

use Illuminate\Foundation\Http\Request;

class AddToCartDTO
{
    public function __construct(
        public readonly int $variantId,
        public readonly int $quantity,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            variantId: (int) $data['variant_id'],
            quantity: (int) $data['quantity'],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            variantId: $data['variant_id'],
            quantity: $data['quantity'],
        );
    }

    public function toArray(): array
    {
        return [
            'variant_id' => $this->variantId,
            'quantity' => $this->quantity,
        ];
    }
}
