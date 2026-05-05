<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuestCheckoutService
{
    public function __construct(
        protected AamarPayService $aamarPayService,
    ) {}

    /**
     * Process guest checkout
     *
     * @throws ValidationException
     */
    public function checkout(array $data, Request $request): array
    {
        return DB::transaction(function () use ($data, $request) {
            // 1. Create or reuse guest record
            $guest = $this->createGuest($data['guest'], $request);

            // 2. Process items and calculate totals
            $processedItems = $this->processItems($data['items']);

            // 3. Calculate financials
            $financials = $this->calculateFinancials($processedItems, $data['orderSummary'] ?? []);

            // 4. Generate order number
            $orderNumber = $this->generateOrderNumber();

            // 5. Build delivery address
            $deliveryAddress = [
                'name' => $data['shippingAddress']['name'],
                'phone' => $data['shippingAddress']['phone'],
                'address_line_1' => $data['shippingAddress']['address'],
                'address_line_2' => null,
                'city' => $data['shippingAddress']['city'],
                'state' => $data['shippingAddress']['district'] ?? null,
                'postal_code' => $data['shippingAddress']['postcode'],
                'country' => 'Bangladesh',
            ];

            // 6. Create order (no user_id, linked to guest instead)
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => null,
                'guest_id' => $guest->id,
                'customer_type' => 'guest',
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['paymentMethod']['id'] === 'cod' ? 'cod' : 'online',
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
                'estimated_delivery_date' => now()->addDays(7),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // 7. Create order items and decrement stock
            foreach ($processedItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
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

                // Decrement stock
                $item['variant']->adjustStock(
                    -$item['quantity'],
                    'Guest order created: ' . $orderNumber,
                    $order->id,
                    Order::class
                );
            }

            // 8. Record status history (no authenticated user for guests)
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'previous_status' => null,
                'notes' => 'Order placed via guest checkout',
                'changed_by' => null,
            ]);

            // 9. Handle payment
            $paymentUrl = null;
            $paymentError = null;

            if ($data['paymentMethod']['id'] === 'aamarpay') {
                $customerInfo = [
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'phone' => $data['shippingAddress']['phone'],
                    'address' => $data['shippingAddress']['address'],
                    'city' => $data['shippingAddress']['city'],
                    'postcode' => $data['shippingAddress']['postcode'],
                    'country' => 'Bangladesh',
                ];

                $result = $this->aamarPayService->initiatePayment($order, $customerInfo);

                if ($result['success'] ?? false) {
                    $paymentUrl = $result['paymentUrl'];
                } else {
                    $paymentError = $result['message'] ?? 'Payment initiation failed';
                }
            }

            // 10. Load relationships for response
            $order->load(['items.variant.product']);

            return [
                'order' => $order,
                'payment_url' => $paymentUrl,
                'payment_error' => $paymentError,
                'guest' => $guest,
            ];
        });
    }

    /**
     * Create a new guest record from checkout details.
     * Always creates a fresh record to avoid any unique constraint issues.
     */
    protected function createGuest(array $guestData, Request $request): Guest
    {
        return Guest::create([
            'name' => $guestData['name'],
            'email' => $guestData['email'],
            'phone' => $guestData['phone'],
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Process and validate items
     *
     * @throws ValidationException
     */
    protected function processItems(array $items): array
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

            // For products with explicit variants, require variant_id to be specified
            if ($product->has_variants) {
                throw ValidationException::withMessages([
                    'items' => "Product ID {$item['product_id']} has multiple variants. Please specify a variant_id.",
                ]);
            }

            // For simple products, find the single/default variant
            return Variant::with(['product', 'attributeValues.attribute'])
                ->where('product_id', $item['product_id'])
                ->first();
        }

        return null;
    }

    /**
     * Calculate financial totals
     */
    protected function calculateFinancials(array $processedItems, array $orderSummary): array
    {
        $settings = Setting::allSettings();

        $subtotal = collect($processedItems)->sum('total_price');
        $discount = 0; // Coupon not supported in v1

        $taxRate = (float) ($settings['tax_rate'] ?? 0);
        $tax = $subtotal * $taxRate;

        // Shipping calculation
        $freeShippingThreshold = (float) ($settings['feature_free_shipping_threshold'] ?? 2000);
        $baseShippingRate = (float) ($settings['shipping_base_rate'] ?? 100);

        // Always calculate shipping server-side — ignore frontend-provided value
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

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$date}-{$random}";
    }
}
