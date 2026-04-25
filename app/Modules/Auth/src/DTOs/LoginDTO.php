<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class LoginDTO
{
    public function __construct(
        public string $mobile,
        public string $password,
        public ?string $deviceName = null
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(
            mobile: $data['mobile'],
            password: $data['password'],
            deviceName: $data['device_name'] ?? null
        );
    }
}
