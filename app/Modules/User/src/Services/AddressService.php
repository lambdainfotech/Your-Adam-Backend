<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Modules\User\Contracts\AddressServiceInterface;
use App\Modules\User\DTOs\AddressDTO;
use App\Modules\User\Models\Address;
use App\Modules\User\Repositories\AddressRepository;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;

class AddressService extends BaseService implements AddressServiceInterface
{
    public function __construct(private AddressRepository $repository)
    {
    }

    public function getAddresses(int $userId): Collection
    {
        return $this->repository->findByUserId($userId);
    }

    public function createAddress(int $userId, AddressDTO $dto): Address
    {
        return $this->transaction(function () use ($userId, $dto) {
            $address = $this->repository->create(array_merge(
                ['user_id' => $userId],
                $dto->toArray()
            ));

            if ($dto->isDefault) {
                $this->repository->setDefault($userId, $address->id);
            }

            return $address;
        });
    }

    public function updateAddress(int $userId, int $addressId, AddressDTO $dto): Address
    {
        return $this->transaction(function () use ($userId, $addressId, $dto) {
            $address = $this->repository->findById($addressId);
            
            if (!$address || $address->user_id !== $userId) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Address not found.');
            }
            
            $address = $this->repository->update($addressId, $dto->toArray());

            if ($dto->isDefault) {
                $this->repository->setDefault($userId, $addressId);
            }

            return $address;
        });
    }

    public function deleteAddress(int $userId, int $addressId): void
    {
        $address = $this->repository->findById($addressId);
        
        if (!$address || $address->user_id !== $userId) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Address not found.');
        }
        
        $this->repository->delete($addressId);
    }

    public function setDefaultAddress(int $userId, int $addressId): void
    {
        $this->repository->setDefault($userId, $addressId);
    }
}
