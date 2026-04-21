<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuestCheckoutRequest;
use App\Services\GuestCheckoutService;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GuestCheckoutController extends Controller
{
    public function __construct(
        protected GuestCheckoutService $service,
    ) {}

    public function store(GuestCheckoutRequest $request): JsonResponse
    {
        $result = $this->service->checkout($request->validated(), $request);

        $order = $result['order'];

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'subtotal' => $order->subtotal,
                    'shipping_amount' => $order->shipping_amount,
                    'tax_amount' => $order->tax_amount,
                    'discount_amount' => $order->discount_amount,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'notes' => $order->notes,
                    'delivery_address' => $order->delivery_address,
                    'estimated_delivery_date' => $order->estimated_delivery_date?->toDateString(),
                    'created_at' => $order->created_at->toDateTimeString(),
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'variant_id' => $item->variant_id,
                        'product_name' => $item->product_name,
                        'variant_sku' => $item->variant_sku,
                        'variant_attributes' => $item->variant_attributes,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'original_price' => $item->original_price,
                        'discount_amount' => $item->discount_amount,
                        'total_price' => $item->total_price,
                    ]),
                ],
                'payment' => [
                    'status' => $order->payment_status,
                    'method' => $order->payment_method,
                    'amount' => $order->total_amount,
                    'payment_url' => $result['payment_url'],
                    'error' => $result['payment_error'],
                ],
                'token' => [
                    'access_token' => $result['token'],
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
            ],
        ], 201);
    }
}
