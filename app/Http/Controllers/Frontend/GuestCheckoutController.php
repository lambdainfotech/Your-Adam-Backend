<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuestCheckoutRequest;
use App\Services\GuestCheckoutService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GuestCheckoutController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected GuestCheckoutService $service,
    ) {}

    public function store(GuestCheckoutRequest $request): JsonResponse
    {
        $result = $this->service->checkout($request->validated(), $request);

        $order = $result['order'];
        $guest = $result['guest'];

        return $this->success([
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
            'guest' => [
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
            ],
        ], 'Order created successfully', 201);
    }
}
