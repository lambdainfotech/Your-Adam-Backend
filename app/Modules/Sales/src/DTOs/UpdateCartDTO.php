<?php

declare(strict_types=1);

namespace App\Modules\Sales\DTOs;

class UpdateCartDTO
{
    public function __construct(
        public readonly int $quantity,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            quantity: (int) $data['quantity'],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            quantity: $data['quantity'],
        );
    }

    public function toArray(): array
    {
        return [
            'quantity' => $this->quantity,
        ];
    }
}
