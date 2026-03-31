<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class RegisterDTO
{
    public function __construct(
        public string $mobile,
        public string $password,
        public string $fullName,
        public ?string $email = null
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(
            mobile: $data['mobile'],
            password: $data['password'],
            fullName: $data['full_name'],
            email: $data['email'] ?? null
        );
    }
}
