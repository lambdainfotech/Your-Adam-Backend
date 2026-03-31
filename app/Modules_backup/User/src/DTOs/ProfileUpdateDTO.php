<?php

declare(strict_types=1);

namespace App\Modules\User\DTOs;

readonly class ProfileUpdateDTO
{
    public function __construct(
        public ?string $fullName,
        public ?string $email,
        public ?string $avatar
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            fullName: $data['full_name'] ?? null,
            email: $data['email'] ?? null,
            avatar: $data['avatar'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'email' => $this->email,
            'avatar' => $this->avatar,
        ];
    }
}
