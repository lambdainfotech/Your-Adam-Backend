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
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Variant;
use App\Modules\Sales\Events\OrderCreated;
use App\Modules\Sales\Events\OrderStatusChanged;
use App\Modules\Sales\Exceptions\EmptyCartException;
use App\Modules\Sales\Exceptions\OrderCancellationException;
use App\Modules\Sales\Models\Order;
use App\Modules\Sales\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

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
                'customer_type' => 'registered',
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
                'currency' => config('app.currency', 'BDT'),
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

                // Decrease stock atomically
                $decremented = $this->variantRepository->decrementStock($item->variant_id, $item->quantity);
                if (!$decremented) {
                    throw new \RuntimeException("Failed to decrement stock for variant {$item->variant_id}");
                }

                // Log inventory movement
                InventoryMovement::logMovement(
                    productId: $variant->product_id,
                    variantId: $item->variant_id,
                    type: InventoryMovement::TYPE_SALE,
                    quantity: -$item->quantity,
                    reason: 'Order created: ' . $orderNumber,
                    referenceId: $order->id,
                    referenceType: Order::class
                );
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

    /**
     * Create order directly from items (like guest checkout but for logged-in users)
     */
    public function createDirect(int $userId, array $data): Order
    {
        return $this->transaction(function () use ($userId, $data) {
            $user = \App\Models\User::find($userId);

            if (!$user) {
                throw new \InvalidArgumentException('User not found.');
            }

            // Process items and calculate totals
            $processedItems = $this->processItemsDirect($data['items']);
            $financials = $this->calculateFinancialsDirect($processedItems, $data['orderSummary'] ?? []);

            // Build delivery address from saved address or inline input
            if (!empty($data['address_id'])) {
                $address = \App\Models\Address::where('id', $data['address_id'])
                    ->where('user_id', $userId)
                    ->first();

                if (!$address) {
                    throw new \InvalidArgumentException('Address not found.');
                }

                $deliveryAddress = [
                    'name' => $address->full_name,
                    'phone' => $address->mobile,
                    'address_line_1' => $address->address_line_1,
                    'address_line_2' => $address->address_line_2,
                    'city' => $address->city,
                    'state' => $address->district,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country ?? 'Bangladesh',
                ];
            } else {
                $shippingAddress = $data['shippingAddress'];
                $deliveryAddress = [
                    'name' => $shippingAddress['name'],
                    'phone' => $shippingAddress['phone'],
                    'address_line_1' => $shippingAddress['address'],
                    'address_line_2' => null,
                    'city' => $shippingAddress['city'],
                    'state' => $shippingAddress['district'] ?? null,
                    'postal_code' => $shippingAddress['postcode'],
                    'country' => 'Bangladesh',
                ];
            }

            $orderNumber = $this->generateOrderNumber();
            $paymentMethod = $data['paymentMethod']['id'] === 'cod' ? 'cod' : 'online';

            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'customer_type' => 'registered',
                'status' => OrderStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
                'payment_method' => $paymentMethod,
                'subtotal' => $financials['subtotal'],
                'discount_amount' => $financials['discount'],
                'coupon_code' => null,
                'coupon_discount' => 0,
                'tax_amount' => $financials['tax'],
                'shipping_amount' => $financials['shipping'],
                'total_amount' => $financials['total'],
                'currency' => config('app.currency', 'BDT'),
                'notes' => $data['note'] ?? null,
                'delivery_address' => $deliveryAddress,
                'billing_address' => $deliveryAddress,
                'estimated_delivery_date' => now()->addDays(config('sales.default_delivery_days', 7)),
            ]);

            foreach ($processedItems as $item) {
                $order->items()->create([
                    'variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'],
                    'variant_sku' => $item['variant_sku'],
                    'variant_attributes' => $item['variant_attributes'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'original_price' => $item['original_price'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['total_price'],
                ]);

                // Decrease stock atomically
                $decremented = $this->variantRepository->decrementStock($item['variant_id'], $item['quantity']);
                if (!$decremented) {
                    throw new \RuntimeException("Failed to decrement stock for variant {$item['variant_id']}");
                }

                // Log inventory movement
                InventoryMovement::logMovement(
                    productId: $item['variant']->product_id,
                    variantId: $item['variant_id'],
                    type: InventoryMovement::TYPE_SALE,
                    quantity: -$item['quantity'],
                    reason: 'Order created: ' . $orderNumber,
                    referenceId: $order->id,
                    referenceType: Order::class
                );
            }

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
                'changed_by' => auth()->id(),
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

                InventoryMovement::logMovement(
                    productId: $item->variant->product_id ?? $item->product_id,
                    variantId: $item->variant_id,
                    type: InventoryMovement::TYPE_RETURN,
                    quantity: $item->quantity,
                    reason: 'Order cancelled: ' . $order->order_number,
                    referenceId: $order->id,
                    referenceType: Order::class
                );
            }

            $this->orderRepository->update($orderId, ['status' => OrderStatus::CANCELLED]);

            $this->orderRepository->addStatusHistory($orderId, [
                'status' => OrderStatus::CANCELLED,
                'previous_status' => $previousStatus,
                'notes' => $reason ?? 'Order cancelled by customer',
                'changed_by' => $userId,
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

    /**
     * Process and validate items for direct order
     */
    protected function processItemsDirect(array $items): array
    {
        $processed = [];

        foreach ($items as $item) {
            $variant = $this->resolveItemVariant($item);

            if (!$variant || !$variant->is_active) {
                $identifier = $item['variant_id'] ?? $item['product_id'];
                $type = !empty($item['variant_id']) ? 'variant' : 'product';
                throw ValidationException::withMessages([
                    'items' => "Product {$type} ID {$identifier} is not available.",
                ]);
            }

            $productName = $variant->product?->name ?? 'Unknown Product';

            if (!$variant->canPurchase($item['quantity'])) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$productName}. Available: {$variant->stock_quantity}, Requested: {$item['quantity']}",
                ]);
            }

            $unitPrice = $variant->final_price;
            $originalPrice = $variant->compare_price ?? $variant->price ?? $variant->product?->base_price ?? $unitPrice;
            $discountAmount = ($originalPrice - $unitPrice) * $item['quantity'];
            $totalPrice = $unitPrice * $item['quantity'];

            $processed[] = [
                'variant_id' => $variant->id,
                'variant' => $variant,
                'product_name' => $productName,
                'variant_sku' => $variant->sku,
                'variant_attributes' => $variant->attributeValues->pluck('value', 'attribute.name')->toArray(),
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'original_price' => $originalPrice,
                'discount_amount' => max(0, $discountAmount),
                'total_price' => $totalPrice,
            ];
        }

        return $processed;
    }

    /**
     * Resolve variant from item data (supports variant_id or product_id)
     */
    protected function resolveItemVariant(array $item): ?Variant
    {
        if (!empty($item['variant_id'])) {
            return Variant::with(['product', 'attributeValues.attribute'])->find($item['variant_id']);
        }

        if (!empty($item['product_id'])) {
            $product = Product::find($item['product_id']);

            if (!$product || !$product->is_active) {
                return null;
            }

            if ($product->has_variants) {
                throw ValidationException::withMessages([
                    'items' => "Product ID {$item['product_id']} has multiple variants. Please specify a variant_id.",
                ]);
            }

            return Variant::with(['product', 'attributeValues.attribute'])
                ->where('product_id', $item['product_id'])
                ->first();
        }

        return null;
    }

    /**
     * Calculate financial totals for direct order
     */
    protected function calculateFinancialsDirect(array $processedItems, array $orderSummary): array
    {
        $settings = Setting::allSettings();

        $subtotal = collect($processedItems)->sum('total_price');
        $discount = 0;

        $taxRate = (float) ($settings['tax_rate'] ?? 0);
        $tax = $subtotal * $taxRate;

        $freeShippingThreshold = (float) ($settings['feature_free_shipping_threshold'] ?? 2000);
        $baseShippingRate = (float) ($settings['shipping_base_rate'] ?? 100);

        $shipping = $subtotal >= $freeShippingThreshold ? 0 : $baseShippingRate;

        $total = max(0, $subtotal - $discount + $tax + $shipping);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2),
        ];
    }

    protected function transaction(\Closure $callback)
    {
        return $this->db->transaction($callback);
    }
}
