<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Setting;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
            // 1. Create user
            $user = $this->createUser($data['guest']);

            // 2. Create address for user
            $address = $this->createAddress($user->id, $data['shippingAddress']);

            // 3. Process items and calculate totals
            $processedItems = $this->processItems($data['items']);

            // 4. Calculate financials
            $financials = $this->calculateFinancials($processedItems, $data['orderSummary'] ?? []);

            // 5. Generate order number
            $orderNumber = $this->generateOrderNumber();

            // 6. Build delivery address
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

            // 7. Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
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

            // 8. Create order items and decrement stock
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

            // 9. Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'previous_status' => null,
                'notes' => 'Order placed via guest checkout',
                'changed_by' => $user->id,
            ]);

            // 10. Handle payment
            $paymentUrl = null;
            $paymentError = null;

            if ($data['paymentMethod']['id'] === 'aamarpay') {
                $customerInfo = [
                    'name' => $user->name,
                    'email' => $user->email,
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

            // 11. Generate JWT token for new user
            $token = JWTAuth::fromUser($user);

            // Load relationships for response
            $order->load(['items.variant.product']);

            return [
                'order' => $order,
                'token' => $token,
                'payment_url' => $paymentUrl,
                'payment_error' => $paymentError,
                'user' => $user,
            ];
        });
    }

    /**
     * Create a new user from guest details
     */
    protected function createUser(array $guest): User
    {
        // Get customer role (default to 3 if not found)
        $customerRoleId = \App\Models\Role::where('slug', 'customer')->value('id') ?? 3;

        // Always create a new guest user — never reuse existing accounts without authentication
        return User::create([
            'name' => $guest['name'],
            'email' => $guest['email'],
            'mobile' => $guest['phone'],
            'password' => $guest['password'],
            'role_id' => $customerRoleId,
            'status' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create address for the newly created user
     */
    protected function createAddress(int $userId, array $shippingAddress): Address
    {
        return Address::create([
            'user_id' => $userId,
            'type' => 'home',
            'full_name' => $shippingAddress['name'],
            'mobile' => $shippingAddress['phone'],
            'address_line_1' => $shippingAddress['address'],
            'city' => $shippingAddress['city'],
            'district' => $shippingAddress['district'] ?? null,
            'postal_code' => $shippingAddress['postcode'],
            'country' => 'Bangladesh',
            'is_default' => true,
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
            $variant = Variant::with(['product', 'attributeValues.attribute'])->find($item['variant_id']);

            if (!$variant || !$variant->is_active) {
                throw ValidationException::withMessages([
                    'items' => "Product variant ID {$item['variant_id']} is not available.",
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
