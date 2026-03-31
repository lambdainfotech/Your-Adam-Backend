<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Modules\User\Contracts\ProfileServiceInterface;
use App\Modules\User\DTOs\ProfileUpdateDTO;
use App\Modules\User\Models\Profile;
use App\Modules\User\Repositories\ProfileRepository;
use App\Services\BaseService;

class ProfileService extends BaseService implements ProfileServiceInterface
{
    public function __construct(private ProfileRepository $repository)
    {
    }

    public function getProfile(int $userId): ?Profile
    {
        return $this->repository->findByUserId($userId);
    }

    public function updateProfile(int $userId, ProfileUpdateDTO $dto): Profile
    {
        return $this->transaction(function () use ($userId, $dto) {
            $profile = $this->repository->findByUserId($userId);

            if (!$profile) {
                return $this->repository->create([
                    'user_id' => $userId,
                    'full_name' => $dto->fullName,
                    'email' => $dto->email,
                    'avatar' => $dto->avatar,
                ]);
            }

            return $this->repository->update($profile->id, $dto->toArray());
        });
    }
}
