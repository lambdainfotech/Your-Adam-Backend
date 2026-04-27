<?php

declare(strict_types=1);

namespace App\Modules\User\DTOs;

readonly class AddressDTO
{
    public function __construct(
        public ?string $type,
        public string $fullName,
        public string $mobile,
        public string $address,
        public ?string $addressLine2,
        public string $city,
        public ?string $district,
        public ?string $postalCode,
        public ?string $landmark,
        public ?string $country,
        public bool $isDefault
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            fullName: $data['full_name'],
            mobile: $data['mobile'],
            address: $data['address'],
            addressLine2: $data['address_line_2'] ?? null,
            city: $data['city'],
            district: $data['district'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            landmark: $data['landmark'] ?? null,
            country: $data['country'] ?? null,
            isDefault: $data['is_default'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type ?? 'home',
            'full_name' => $this->fullName,
            'mobile' => $this->mobile,
            'address_line_1' => $this->address,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postalCode,
            'landmark' => $this->landmark,
            'country' => $this->country,
            'is_default' => $this->isDefault,
        ];
    }
}
