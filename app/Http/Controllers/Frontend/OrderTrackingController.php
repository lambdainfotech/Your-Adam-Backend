<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosOrder;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTrackingController extends Controller
{
    use ApiResponse;

    /**
     * Track order by order number and phone number.
     *
     * Works for both online orders (guest & registered) and POS orders.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $normalizedPhone = $this->normalizePhone($validated['phone']);
        $orderNumber = $validated['order_number'];

        // 1. Try to find as regular online order first
        $order = $this->findOnlineOrder($orderNumber, $normalizedPhone);

        if ($order) {
            return $this->success($this->formatOnlineOrderTracking($order), 'Tracking information retrieved successfully');
        }

        // 2. Fallback to POS order
        $posOrder = $this->findPosOrder($orderNumber, $normalizedPhone);

        if ($posOrder) {
            return $this->success($this->formatPosOrderTracking($posOrder), 'Tracking information retrieved successfully');
        }

        return $this->error('Order not found. Please check your order number and phone number.', 404);
    }

    /**
     * Find online order (guest or registered) by order number and phone.
     */
    private function findOnlineOrder(string $orderNumber, string $normalizedPhone): ?Order
    {
        $orders = Order::with(['items.variant.product', 'statusHistory', 'courierAssignment.courier', 'guest', 'user'])
            ->where('order_number', $orderNumber)
            ->get();

        foreach ($orders as $order) {
            if ($this->phoneMatches($order, $normalizedPhone)) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Find POS order by order number and phone.
     */
    private function findPosOrder(string $orderNumber, string $normalizedPhone): ?PosOrder
    {
        $posOrder = PosOrder::with(['items', 'statusHistory', 'courier'])
            ->where('order_number', $orderNumber)
            ->first();

        if ($posOrder && $this->normalizePhone($posOrder->customer_phone) === $normalizedPhone) {
            return $posOrder;
        }

        return null;
    }

    /**
     * Check if any phone associated with the order matches the provided phone.
     */
    private function phoneMatches(Order $order, string $normalizedPhone): bool
    {
        // Check guest phone
        if ($order->guest && $this->normalizePhone($order->guest->phone) === $normalizedPhone) {
            return true;
        }

        // Check user mobile
        if ($order->user && $this->normalizePhone($order->user->mobile) === $normalizedPhone) {
            return true;
        }

        // Check delivery address phone
        $deliveryPhone = $order->delivery_address['phone'] ?? null;
        if ($deliveryPhone && $this->normalizePhone($deliveryPhone) === $normalizedPhone) {
            return true;
        }

        // Check billing address phone
        $billingPhone = $order->billing_address['phone'] ?? null;
        if ($billingPhone && $this->normalizePhone($billingPhone) === $normalizedPhone) {
            return true;
        }

        return false;
    }

    /**
     * Normalize phone number for comparison.
     * Handles Bangladesh formats: 01XXXXXXXXX, +8801XXXXXXXXX, 8801XXXXXXXXX
     */
    private function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Convert +880 or 880 prefix to local 01 format for Bangladesh numbers
        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            return '0' . substr($digits, 3);
        }

        return $digits;
    }

    /**
     * Format online order tracking response.
     */
    private function formatOnlineOrderTracking(Order $order): array
    {
        $courier = $order->courierAssignment;

        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'item_count' => $order->items->sum('quantity'),
            'estimated_delivery' => $order->estimated_delivery_date?->toDateString(),
            'delivered_at' => $order->delivered_at?->toDateTimeString(),
            'created_at' => $order->created_at->toDateTimeString(),
            'customer' => [
                'name' => $order->customer()?->name ?? 'Guest',
                'phone' => $order->customer()?->phone ?? $order->customer()?->mobile ?? ($order->delivery_address['phone'] ?? null),
            ],
            'delivery_address' => $order->delivery_address,
            'courier' => $courier ? [
                'name' => $courier->courier?->name,
                'tracking_number' => $courier->tracking_number,
                'tracking_url' => $courier->tracking_url,
                'assigned_at' => $courier->assigned_at?->toDateTimeString(),
                'picked_up_at' => $courier->picked_up_at?->toDateTimeString(),
                'delivered_at' => $courier->delivered_at?->toDateTimeString(),
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'variant_sku' => $item->variant_sku,
                'variant_attributes' => $item->variant_attributes,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]),
            'timeline' => $this->buildTimeline($order->statusHistory),
        ];
    }

    /**
     * Format POS order tracking response.
     */
    private function formatPosOrderTracking(PosOrder $order): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
            'delivery_status' => $order->delivery_status,
            'total_amount' => $order->total_amount,
            'currency' => 'BDT',
            'item_count' => $order->items->sum('quantity'),
            'estimated_delivery' => $order->estimated_delivery_date?->toDateString(),
            'delivered_at' => $order->delivered_at?->toDateTimeString(),
            'created_at' => $order->created_at->toDateTimeString(),
            'customer' => [
                'name' => $order->customer_name ?? 'Walk-in Customer',
                'phone' => $order->customer_phone,
            ],
            'delivery_address' => $order->delivery_address,
            'courier' => $order->courier ? [
                'name' => $order->courier->name,
                'tracking_number' => $order->tracking_number,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]),
            'timeline' => $this->buildTimeline($order->statusHistory),
        ];
    }

    /**
     * Build a formatted timeline from status history.
     *
     * @param \Illuminate\Database\Eloquent\Collection $statusHistory
     */
    private function buildTimeline($statusHistory): array
    {
        return $statusHistory->map(fn ($history) => [
            'status' => $history->status,
            'status_label' => ucfirst(str_replace('_', ' ', $history->status)),
            'previous_status' => $history->previous_status ? ucfirst(str_replace('_', ' ', $history->previous_status)) : null,
            'notes' => $history->notes,
            'created_at' => $history->created_at->toDateTimeString(),
        ])->values()->all();
    }
}
