<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Contracts\OrderServiceInterface;
use App\Modules\Sales\DTOs\CreateOrderDTO;
use App\Modules\Sales\Http\Requests\CancelOrderRequest;
use App\Modules\Sales\Http\Requests\CreateOrderRequest;
use App\Modules\Sales\Http\Requests\CustomerOrderRequest;
use App\Services\AamarPayService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $service,
        protected AamarPayService $aamarPayService,
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

    /**
     * Create order directly from items (same format as guest checkout)
     */
    public function storeDirect(CustomerOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = $this->service->createDirect(
            $request->user()->id,
            $data
        );

        $paymentUrl = null;
        $paymentError = null;

        // Handle aamarpay payment initiation
        if (($data['paymentMethod']['id'] ?? '') === 'aamarpay') {
            $shippingAddress = $data['shippingAddress'] ?? [];
            $customerInfo = [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $shippingAddress['phone'] ?? $request->user()->phone ?? '01700000000',
                'address' => $shippingAddress['address'] ?? '',
                'city' => $shippingAddress['city'] ?? 'Dhaka',
                'postcode' => $shippingAddress['postcode'] ?? '1200',
                'country' => 'Bangladesh',
            ];

            $result = $this->aamarPayService->initiatePayment($order, $customerInfo);

            if ($result['success'] ?? false) {
                $paymentUrl = $result['paymentUrl'];
            } else {
                $paymentError = $result['message'] ?? 'Payment initiation failed';
            }
        }

        return $this->createdResponse([
            'order' => $order,
            'payment' => [
                'status' => $order->payment_status,
                'method' => $order->payment_method,
                'amount' => $order->total_amount,
                'payment_url' => $paymentUrl,
                'error' => $paymentError,
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $order = $this->service->getById(
                $request->user()->id,
                (int) $id
            );

            return $this->successResponse($order);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function track(Request $request, string $id): JsonResponse
    {
        try {
            $tracking = $this->service->getTrackingForUser($request->user()->id, (int) $id);

            return $this->successResponse($tracking);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function cancel(CancelOrderRequest $request, string $id): JsonResponse
    {
        try {
            $order = $this->service->cancel(
                $request->user()->id,
                (int) $id,
                $request->validated()['reason'] ?? null
            );

            return $this->successResponse($order);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
