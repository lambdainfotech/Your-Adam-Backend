<?php

declare(strict_types=1);

namespace App\Modules\Address\Repositories;

use App\Modules\Address\Contracts\AddressRepositoryInterface;
use App\Modules\Address\Models\Address;

class AddressRepository implements AddressRepositoryInterface
{
    public function __construct(protected Address $model)
    {
    }

    public function find(int $id): ?Address
    {
        return $this->model->find($id);
    }

    public function findByIdAndUser(int $id, int $userId): ?Address
    {
        return $this->model->where('id', $id)->where('user_id', $userId)->first();
    }

    public function getByUser(int $userId): array
    {
        return $this->model->where('user_id', $userId)->get()->all();
    }
}
