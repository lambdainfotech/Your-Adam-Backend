<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Modules\Address\Contracts\AddressRepositoryInterface;
use App\Modules\Catalog\Contracts\VariantRepositoryInterface;
use App\Modules\Sales\Contracts\CartServiceInterface;
use App\Modules\Sales\Contracts\CouponServiceInterface;
use App\Modules\Sales\Contracts\OrderServiceInterface;
use App\Modules\Sales\DTOs\CreateOrderDTO;
use App\Modules\Sales\Enums\OrderStatus;
use App\Modules\Sales\Enums\PaymentStatus;
use App\Modules\Sales\Events\OrderCreated;
use App\Modules\Sales\Events\OrderStatusChanged;
use App\Modules\Sales\Exceptions\EmptyCartException;
use App\Modules\Sales\Exceptions\OrderCancellationException;
use App\Modules\Sales\Models\Order;
use App\Modules\Sales\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected CartServiceInterface $cartService,
        protected CouponServiceInterface $couponService,
        protected AddressRepositoryInterface $addressRepository,
        protected VariantRepositoryInterface $variantRepository,
        protected DatabaseManager $db,
    ) {}

    public function create(int $userId, CreateOrderDTO $dto): Order
    {
        return $this->transaction(function () use ($userId, $dto) {
            $cart = $this->cartService->getCart($userId);

            if ($cart->items->isEmpty()) {
                throw new EmptyCartException();
            }

            $address = $this->addressRepository->findByIdAndUser($dto->addressId, $userId);

            if ($address === null) {
                throw new \InvalidArgumentException('Address not found.');
            }

            $orderNumber = $this->generateOrderNumber();
            $subtotal = $this->calculateSubtotal($cart);
            $discount = $dto->couponCode ? $this->calculateDiscount($subtotal, $dto->couponCode, $userId) : 0;
            $tax = $this->calculateTax($subtotal);
            $shipping = $this->calculateShipping($cart);
            $total = max(0, $subtotal - $discount + $tax + $shipping);

            $deliveryAddress = [
                'name' => $address->name,
                'phone' => $address->phone,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
            ];

            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'payment_method' => $dto->paymentMethod,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $dto->couponCode,
                'coupon_discount' => $discount,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'total_amount' => $total,
                'currency' => config('app.currency', 'USD'),
                'notes' => $dto->notes,
                'delivery_address' => $deliveryAddress,
                'billing_address' => $deliveryAddress,
                'estimated_delivery_date' => now()->addDays(config('sales.default_delivery_days', 7)),
            ]);

            foreach ($cart->items as $item) {
                $variant = $item->variant;

                $order->items()->create([
                    'variant_id' => $item->variant_id,
                    'product_name' => $variant->product->name,
                    'variant_sku' => $variant->sku,
                    'variant_attributes' => $variant->attribute_values->pluck('value', 'attribute.name')->toArray(),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'original_price' => $variant->compare_price ?? $item->unit_price,
                    'discount_amount' => 0,
                    'total_price' => $item->quantity * $item->unit_price,
                ]);

                // Decrease stock
                $this->variantRepository->decrementStock($item->variant_id, $item->quantity);
            }

            // Record coupon usage if coupon was applied
            if ($dto->couponCode && $discount > 0) {
                $coupon = $this->couponService->getByCode($dto->couponCode);
                if ($coupon) {
                    $this->couponService->recordUsage($coupon->id, $userId, $order->id, $discount);
                }
            }

            // Clear cart
            $this->cartService->clearCart($userId);

            // Dispatch event
            OrderCreated::dispatch($order);

            return $order->fresh(['items']);
        });
    }

    public function getById(int $userId, int $orderId): Order
    {
        $order = $this->orderRepository->findByIdAndUser($orderId, $userId);

        if ($order === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found.');
        }

        return $order;
    }

    public function getByNumber(string $orderNumber): ?Order
    {
        return $this->orderRepository->findByNumber($orderNumber);
    }

    public function list(int $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->listByUserId($userId, $filters);
    }

    public function updateStatus(int $orderId, OrderStatus $status, ?string $notes = null): Order
    {
        return $this->transaction(function () use ($orderId, $status, $notes) {
            $order = $this->orderRepository->find($orderId);

            if ($order === null) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found.');
            }

            $previousStatus = $order->status;

            if ($previousStatus === $status) {
                return $order;
            }

            $this->orderRepository->update($orderId, ['status' => $status]);

            $this->orderRepository->addStatusHistory($orderId, [
                'status' => $status,
                'previous_status' => $previousStatus,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            OrderStatusChanged::dispatch($order->fresh(), $previousStatus, $status);

            return $order->fresh();
        });
    }

    public function cancel(int $userId, int $orderId, ?string $reason = null): Order
    {
        return $this->transaction(function () use ($userId, $orderId, $reason) {
            $order = $this->getById($userId, $orderId);

            if (! $order->canCancel()) {
                throw new OrderCancellationException('This order cannot be cancelled.');
            }

            $previousStatus = $order->status;

            // Restore stock
            foreach ($order->items as $item) {
                $this->variantRepository->incrementStock($item->variant_id, $item->quantity);
            }

            $this->orderRepository->update($orderId, ['status' => OrderStatus::CANCELLED]);

            $this->orderRepository->addStatusHistory($orderId, [
                'status' => OrderStatus::CANCELLED,
                'previous_status' => $previousStatus,
                'notes' => $reason ?? 'Order cancelled by customer',
                'created_by' => $userId,
            ]);

            OrderStatusChanged::dispatch($order->fresh(), $previousStatus, OrderStatus::CANCELLED);

            return $order->fresh();
        });
    }

    public function getTracking(string $orderNumber): array
    {
        $order = $this->orderRepository->findByNumber($orderNumber);

        if ($order === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found.');
        }

        return $this->formatTracking($order);
    }

    public function getTrackingForUser(int $userId, int $orderId): array
    {
        $order = $this->orderRepository->findByIdAndUser($orderId, $userId);

        if ($order === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found.');
        }

        return $this->formatTracking($order);
    }

    private function formatTracking(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'estimated_delivery' => $order->estimated_delivery_date?->toDateString(),
            'tracking_history' => $order->statusHistory->map(fn ($history) => [
                'status' => $history->status->value,
                'status_label' => $history->status->label(),
                'notes' => $history->notes,
                'created_at' => $history->created_at->toDateTimeString(),
            ]),
        ];
    }

    protected function generateOrderNumber(): string
    {
        $prefix = config('sales.order_prefix', 'ORD');
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$date}-{$random}";
    }

    protected function calculateSubtotal($cart): float
    {
        return $cart->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }

    protected function calculateDiscount(float $subtotal, string $couponCode, int $userId): float
    {
        try {
            return $this->couponService->apply($couponCode, $userId, $subtotal);
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function calculateTax(float $subtotal): float
    {
        $taxRate = config('sales.tax_rate', 0);

        return $subtotal * $taxRate;
    }

    protected function calculateShipping($cart): float
    {
        $baseShipping = config('sales.base_shipping', 0);
        $freeShippingThreshold = config('sales.free_shipping_threshold');

        if ($freeShippingThreshold !== null && $cart->subtotal >= $freeShippingThreshold) {
            return 0;
        }

        return $baseShipping;
    }

    protected function transaction(\Closure $callback)
    {
        return $this->db->transaction($callback);
    }
}
