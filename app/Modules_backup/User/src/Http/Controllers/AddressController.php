<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Modules\Shared\Http\Controllers\BaseController;
use App\Modules\User\Contracts\AddressServiceInterface;
use App\Modules\User\DTOs\AddressDTO;
use App\Modules\User\Http\Requests\AddressRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function __construct(private AddressServiceInterface $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->service->getAddresses($request->user()->id)
        );
    }

    public function store(AddressRequest $request): JsonResponse
    {
        return $this->createdResponse(
            $this->service->createAddress(
                $request->user()->id,
                AddressDTO::fromRequest($request->validated())
            )
        );
    }

    public function update(AddressRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            $this->service->updateAddress(
                $request->user()->id,
                $id,
                AddressDTO::fromRequest($request->validated())
            )
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteAddress($request->user()->id, $id);

        return $this->noContentResponse();
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $this->service->setDefaultAddress($request->user()->id, $id);

        return $this->successResponse();
    }
}
