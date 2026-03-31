<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class OTPSendDTO
{
    public function __construct(
        public string $mobile,
        public string $purpose
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(
            mobile: $data['mobile'],
            purpose: $data['purpose']
        );
    }
}
