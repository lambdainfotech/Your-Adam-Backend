<?php

declare(strict_types=1);

namespace App\Modules\User\Contracts;

use App\Modules\User\DTOs\ProfileUpdateDTO;
use App\Modules\User\Models\Profile;

interface ProfileServiceInterface
{
    public function getProfile(int $userId): ?Profile;

    public function updateProfile(int $userId, ProfileUpdateDTO $dto): Profile;
}
