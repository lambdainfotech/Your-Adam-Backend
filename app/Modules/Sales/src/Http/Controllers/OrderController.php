<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Contracts\OrderServiceInterface;
use App\Modules\Sales\DTOs\CreateOrderDTO;
use App\Modules\Sales\Http\Requests\CancelOrderRequest;
use App\Modules\Sales\Http\Requests\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->service->list(
            $request->user()->id,
            $request->all()
        );

        return $this->paginatedResponse($orders);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->service->create(
            $request->user()->id,
            CreateOrderDTO::fromRequest($request->validated())
        );

        return $this->createdResponse($order);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $order = $this->service->getById(
            $request->user()->id,
            (int) $id
        );

        return $this->successResponse($order);
    }

    public function track(Request $request, string $id): JsonResponse
    {
        $tracking = $this->service->getTracking($id);

        return $this->successResponse($tracking);
    }

    public function cancel(CancelOrderRequest $request, string $id): JsonResponse
    {
        $order = $this->service->cancel(
            $request->user()->id,
            (int) $id,
            $request->validated()['reason'] ?? null
        );

        return $this->successResponse($order);
    }
}
