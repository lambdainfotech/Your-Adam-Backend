<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\Profile;
use App\Repositories\BaseRepository;

class ProfileRepository extends BaseRepository
{
    protected function getCachePrefix(): string
    {
        return 'profiles';
    }

    public function findByUserId(int $userId): ?Profile
    {
        return $this->findBy(['user_id' => $userId]);
    }
}
