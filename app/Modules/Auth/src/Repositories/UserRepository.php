<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Models\User;
use App\Modules\Core\Abstracts\BaseRepository;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'users';
    }

    public function findByMobile(string $mobile): ?User
    {
        return $this->findBy(['mobile' => $mobile]);
    }

    public function createWithProfile(array $userData, array $profileData): User
    {
        $user = $this->create($userData);

        $user->profile()->create($profileData);

        return $user->fresh(['profile', 'role']);
    }

    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, ['last_login_at' => now()]);
    }

    public function updatePassword(int $userId, string $password): void
    {
        $this->update($userId, ['password' => Hash::make($password)]);
    }
}
