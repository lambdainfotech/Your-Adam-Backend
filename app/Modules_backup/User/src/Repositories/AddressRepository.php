<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\Address;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository extends BaseRepository
{
    protected function getCachePrefix(): string
    {
        return 'addresses';
    }

    public function findByUserId(int $userId): Collection
    {
        return $this->findByCriteria(['user_id' => $userId], ['is_default' => 'desc']);
    }

    public function setDefault(int $userId, int $addressId): void
    {
        $this->query()->where('user_id', $userId)->update(['is_default' => false]);
        $this->query()->where('id', $addressId)->where('user_id', $userId)->update(['is_default' => true]);
    }
}
