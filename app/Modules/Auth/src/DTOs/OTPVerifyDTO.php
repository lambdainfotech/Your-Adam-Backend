<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

readonly class OTPVerifyDTO
{
    public function __construct(
        public string $mobile,
        public string $otp,
        public string $reference,
        public bool $isRegistration,
        public ?string $password = null,
        public ?string $fullName = null,
        public ?string $email = null
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(
            mobile: $data['mobile'],
            otp: $data['otp'],
            reference: $data['reference'],
            isRegistration: $data['is_registration'] ?? false,
            password: $data['password'] ?? null,
            fullName: $data['full_name'] ?? null,
            email: $data['email'] ?? null
        );
    }
}
