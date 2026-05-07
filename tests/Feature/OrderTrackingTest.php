<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_track_order_with_guest_phone(): void
    {
        $guest = Guest::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01700000000',
        ]);

        $product = Product::factory()->create(['is_active' => true, 'has_variants' => false]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'stock_quantity' => 100,
            'price' => 1250,
        ]);

        $order = Order::factory()->create([
            'guest_id' => $guest->id,
            'customer_type' => 'guest',
            'order_number' => 'ORD-20260501-ABC123',
            'status' => 'shipped',
            'payment_status' => 'paid',
            'total_amount' => 2500,
            'currency' => 'BDT',
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '01700000000',
                'address' => '123 Test Street',
                'city' => 'Dhaka',
            ],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 1250,
            'total_price' => 2500,
        ]);

        OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
            'previous_status' => null,
            'notes' => 'Order placed',
        ]);

        OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'status' => 'shipped',
            'previous_status' => 'pending',
            'notes' => 'Handed to courier',
        ]);

        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260501-ABC123',
            'phone' => '01700000000',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', 'ORD-20260501-ABC123')
            ->assertJsonPath('data.status', 'shipped')
            ->assertJsonPath('data.status_label', 'Shipped')
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.customer.phone', '01700000000')
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonCount(2, 'data.timeline');
    }

    public function test_track_order_with_registered_user_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'mobile' => '01811111111',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'customer_type' => 'registered',
            'order_number' => 'ORD-20260502-DEF456',
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total_amount' => 1500,
            'currency' => 'BDT',
            'delivery_address' => [
                'name' => 'Jane Doe',
                'phone' => '01811111111',
                'address' => '456 Main Road',
                'city' => 'Chittagong',
            ],
        ]);

        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260502-DEF456',
            'phone' => '01811111111',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', 'ORD-20260502-DEF456')
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.customer.name', 'Jane Doe')
            ->assertJsonPath('data.customer.phone', '01811111111');
    }

    public function test_track_order_with_bangladesh_international_phone_format(): void
    {
        $guest = Guest::factory()->create([
            'phone' => '01700000000',
        ]);

        Order::factory()->create([
            'guest_id' => $guest->id,
            'customer_type' => 'guest',
            'order_number' => 'ORD-20260503-GHI789',
            'status' => 'pending',
        ]);

        // Test with +880 prefix
        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260503-GHI789',
            'phone' => '+8801700000000',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', 'ORD-20260503-GHI789');

        // Test with 880 prefix
        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260503-GHI789',
            'phone' => '8801700000000',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_track_order_returns_404_for_invalid_order(): void
    {
        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-NONEXISTENT',
            'phone' => '01700000000',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Order not found. Please check your order number and phone number.');
    }

    public function test_track_order_returns_404_for_wrong_phone(): void
    {
        $guest = Guest::factory()->create([
            'phone' => '01700000000',
        ]);

        Order::factory()->create([
            'guest_id' => $guest->id,
            'customer_type' => 'guest',
            'order_number' => 'ORD-20260504-JKL012',
        ]);

        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260504-JKL012',
            'phone' => '01999999999',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_track_order_validates_required_fields(): void
    {
        $response = $this->postJson('/api/track-order', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number', 'phone']);
    }

    public function test_track_order_matches_delivery_address_phone(): void
    {
        $user = User::factory()->create([
            'mobile' => '01811111111',
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'customer_type' => 'registered',
            'order_number' => 'ORD-20260505-MNO345',
            'delivery_address' => [
                'name' => 'Different Person',
                'phone' => '01922222222',
                'address' => '789 Side Street',
                'city' => 'Sylhet',
            ],
        ]);

        // Should match delivery address phone even if user mobile is different
        $response = $this->postJson('/api/track-order', [
            'order_number' => 'ORD-20260505-MNO345',
            'phone' => '01922222222',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', 'ORD-20260505-MNO345');
    }

    public function test_order_details_by_order_number(): void
    {
        $guest = Guest::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01700000000',
        ]);

        $product = Product::factory()->create(['is_active' => true, 'has_variants' => false]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'stock_quantity' => 100,
            'price' => 1250,
        ]);

        $order = Order::factory()->create([
            'guest_id' => $guest->id,
            'customer_type' => 'guest',
            'order_number' => 'ORD-DETAILS-001',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'subtotal' => 2500,
            'shipping_amount' => 100,
            'total_amount' => 2600,
            'currency' => 'BDT',
            'coupon_code' => 'SAVE10',
            'delivery_address' => [
                'name' => 'John Doe',
                'phone' => '01700000000',
                'address' => '123 Test Street',
                'city' => 'Dhaka',
            ],
            'billing_address' => [
                'name' => 'John Doe',
                'phone' => '01700000000',
                'address' => '123 Test Street',
                'city' => 'Dhaka',
            ],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 1250,
            'total_price' => 2500,
        ]);

        OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
            'previous_status' => null,
            'notes' => 'Order placed',
        ]);

        $response = $this->postJson('/api/order-details', [
            'order_number' => 'ORD-DETAILS-001',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.order_number', 'ORD-DETAILS-001')
            ->assertJsonPath('data.order.status', 'confirmed')
            ->assertJsonPath('data.order.payment_status', 'paid')
            ->assertJsonPath('data.order.subtotal', '2500.00')
            ->assertJsonPath('data.order.shipping_amount', '100.00')
            ->assertJsonPath('data.order.total_amount', '2600.00')
            ->assertJsonPath('data.order.coupon_code', 'SAVE10')
            ->assertJsonPath('data.customer.name', 'John Doe')
            ->assertJsonPath('data.customer.email', 'john@example.com')
            ->assertJsonPath('data.customer.phone', '01700000000')
            ->assertJsonPath('data.delivery_address.city', 'Dhaka')
            ->assertJsonPath('data.billing_address.city', 'Dhaka')
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonCount(1, 'data.timeline')
            ->assertJsonPath('data.items.0.product_name', 'Test Product');
    }

    public function test_order_details_by_order_id(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-DETAILS-002',
            'status' => 'shipped',
        ]);

        $response = $this->postJson('/api/order-details', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.order_number', 'ORD-DETAILS-002')
            ->assertJsonPath('data.order.status', 'shipped');
    }

    public function test_order_details_returns_404_for_invalid_order(): void
    {
        $response = $this->postJson('/api/order-details', [
            'order_number' => 'ORD-NONEXISTENT',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Order not found.');
    }

    public function test_order_details_validates_required_fields(): void
    {
        $response = $this->postJson('/api/order-details', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'order_number']);
    }
}
