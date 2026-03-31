<?php

declare(strict_types=1);

namespace App\Modules\Address\Contracts;

use App\Modules\Address\Models\Address;

interface AddressRepositoryInterface
{
    public function find(int $id): ?Address;

    public function findByIdAndUser(int $id, int $userId): ?Address;

    public function getByUser(int $userId): array;
}
