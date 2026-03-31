<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Contracts\CartServiceInterface;
use App\Modules\Sales\DTOs\AddToCartDTO;
use App\Modules\Sales\DTOs\ApplyCouponDTO;
use App\Modules\Sales\DTOs\UpdateCartDTO;
use App\Modules\Sales\Http\Requests\AddToCartRequest;
use App\Modules\Sales\Http\Requests\ApplyCouponRequest;
use App\Modules\Sales\Http\Requests\UpdateCartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->service->getCart($request->user()->id);

        return $this->successResponse([
            'cart' => $cart,
            'summary' => $this->service->getCartSummary($request->user()->id),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $item = $this->service->addItem(
            $request->user()->id,
            AddToCartDTO::fromRequest($request->validated())
        );

        return $this->createdResponse($item);
    }

    public function update(UpdateCartRequest $request, int $id): JsonResponse
    {
        $item = $this->service->updateItem(
            $request->user()->id,
            $id,
            UpdateCartDTO::fromRequest($request->validated())
        );

        return $this->successResponse($item);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->removeItem($request->user()->id, $id);

        return $this->noContentResponse();
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $cart = $this->service->applyCoupon(
            $request->user()->id,
            ApplyCouponDTO::fromRequest($request->validated())
        );

        return $this->successResponse([
            'cart' => $cart,
            'summary' => $this->service->getCartSummary($request->user()->id),
        ]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $this->service->removeCoupon($request->user()->id);

        return $this->noContentResponse();
    }

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->service->getCartSummary($request->user()->id);

        return $this->successResponse($summary);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->service->clearCart($request->user()->id);

        return $this->noContentResponse();
    }
}
