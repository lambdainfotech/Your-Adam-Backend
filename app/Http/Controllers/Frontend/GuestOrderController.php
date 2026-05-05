<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestOrderController extends Controller
{
    use ApiResponse;

    /**
     * Get guest order details by order number + email/phone verification.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with(['items.variant.product', 'statusHistory'])
            ->where('order_number', $orderNumber)
            ->where('customer_type', 'guest')
            ->whereHas('guest', function ($query) use ($validated) {
                $query->where('email', $validated['email'])
                      ->where('phone', $validated['phone']);
            })
            ->first();

        if (!$order) {
            return $this->error('Order not found. Please check your order number, email, and phone.', 404);
        }

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
                'status_history' => $order->statusHistory->map(fn ($history) => [
                    'status' => $history->status,
                    'previous_status' => $history->previous_status,
                    'notes' => $history->notes,
                    'created_at' => $history->created_at->toDateTimeString(),
                ]),
            ],
            'guest' => [
                'name' => $order->guest->name,
                'email' => $order->guest->email,
                'phone' => $order->guest->phone,
            ],
        ], 'Order retrieved successfully');
    }

    /**
     * Track guest order by order number + email/phone verification.
     */
    public function track(Request $request, string $orderNumber): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with('statusHistory')
            ->where('order_number', $orderNumber)
            ->where('customer_type', 'guest')
            ->whereHas('guest', function ($query) use ($validated) {
                $query->where('email', $validated['email'])
                      ->where('phone', $validated['phone']);
            })
            ->first();

        if (!$order) {
            return $this->error('Order not found. Please check your order number, email, and phone.', 404);
        }

        return $this->success([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => ucfirst($order->status),
            'payment_status' => $order->payment_status,
            'estimated_delivery' => $order->estimated_delivery_date?->toDateString(),
            'tracking_history' => $order->statusHistory->map(fn ($history) => [
                'status' => $history->status,
                'status_label' => ucfirst($history->status),
                'notes' => $history->notes,
                'created_at' => $history->created_at->toDateTimeString(),
            ]),
        ], 'Tracking information retrieved successfully');
    }
}
