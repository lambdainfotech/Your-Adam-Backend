<?php

declare(strict_types=1);

namespace App\Modules\User\Contracts;

use App\Modules\User\DTOs\AddressDTO;
use App\Modules\User\Models\Address;
use Illuminate\Database\Eloquent\Collection;

interface AddressServiceInterface
{
    public function getAddresses(int $userId): Collection;

    public function getDefaultAddress(int $userId): ?Address;

    public function createAddress(int $userId, AddressDTO $dto): Address;

    public function updateAddress(int $userId, int $addressId, AddressDTO $dto): Address;

    public function deleteAddress(int $userId, int $addressId): void;

    public function setDefaultAddress(int $userId, int $addressId): void;
}
