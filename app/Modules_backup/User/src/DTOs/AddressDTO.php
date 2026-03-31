<?php

declare(strict_types=1);

namespace App\Modules\User\DTOs;

readonly class AddressDTO
{
    public function __construct(
        public string $type,
        public string $fullName,
        public string $mobile,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $district,
        public string $postalCode,
        public ?string $landmark,
        public bool $isDefault
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            type: $data['type'],
            fullName: $data['full_name'],
            mobile: $data['mobile'],
            addressLine1: $data['address_line_1'],
            addressLine2: $data['address_line_2'] ?? null,
            city: $data['city'],
            district: $data['district'],
            postalCode: $data['postal_code'],
            landmark: $data['landmark'] ?? null,
            isDefault: $data['is_default'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'full_name' => $this->fullName,
            'mobile' => $this->mobile,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postalCode,
            'landmark' => $this->landmark,
            'is_default' => $this->isDefault,
        ];
    }
}
