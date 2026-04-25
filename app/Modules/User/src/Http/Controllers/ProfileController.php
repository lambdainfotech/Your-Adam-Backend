<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Modules\Shared\Http\Controllers\BaseController;
use App\Modules\User\Contracts\ProfileServiceInterface;
use App\Modules\User\DTOs\ProfileUpdateDTO;
use App\Modules\User\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function __construct(private ProfileServiceInterface $service)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->service->getProfile($request->user()->id)
        );
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->service->updateProfile(
                $request->user()->id,
                ProfileUpdateDTO::fromRequest($request->validated())
            )
        );
    }
}
