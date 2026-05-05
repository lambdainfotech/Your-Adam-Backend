<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed minimal required data
        $this->artisan('migrate');
    }

    public function test_guest_checkout_creates_order_without_user(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'has_variants' => false]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'stock_quantity' => 100,
            'price' => 100,
        ]);

        $payload = [
            'guest' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '01700000000',
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2,
                ],
            ],
            'shippingAddress' => [
                'name' => 'John Doe',
                'phone' => '01700000000',
                'address' => '123 Test Street',
                'city' => 'Dhaka',
                'postcode' => '1200',
            ],
            'paymentMethod' => [
                'id' => 'cod',
            ],
        ];

        $response = $this->postJson('/api/guest-checkout', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order created successfully');

        $order = Order::latest()->first();
        $this->assertNull($order->user_id);
        $this->assertNotNull($order->guest_id);
        $this->assertEquals('guest', $order->customer_type);

        $guest = Guest::find($order->guest_id);
        $this->assertEquals('john@example.com', $guest->email);
        $this->assertEquals('John Doe', $guest->name);
    }

    public function test_duplicate_guest_email_does_not_cause_error(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'has_variants' => false]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'stock_quantity' => 100,
            'price' => 100,
        ]);

        $payload = [
            'guest' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '01711111111',
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                ],
            ],
            'shippingAddress' => [
                'name' => 'Jane Doe',
                'phone' => '01711111111',
                'address' => '456 Test Ave',
                'city' => 'Dhaka',
                'postcode' => '1200',
            ],
            'paymentMethod' => [
                'id' => 'cod',
            ],
        ];

        // First checkout
        $response1 = $this->postJson('/api/guest-checkout', $payload);
        $response1->assertStatus(201);

        // Second checkout with same email — should NOT fail
        $response2 = $this->postJson('/api/guest-checkout', $payload);
        $response2->assertStatus(201);

        $this->assertEquals(2, Guest::where('email', 'jane@example.com')->count());
        $this->assertEquals(2, Order::where('customer_type', 'guest')->count());
    }

    public function test_guest_can_lookup_order_by_number_and_email_phone(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'has_variants' => false]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'is_active' => true,
            'stock_quantity' => 100,
            'price' => 100,
        ]);

        $payload = [
            'guest' => [
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'phone' => '01722222222',
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                ],
            ],
            'shippingAddress' => [
                'name' => 'Alice Smith',
                'phone' => '01722222222',
                'address' => '789 Test Blvd',
                'city' => 'Dhaka',
                'postcode' => '1200',
            ],
            'paymentMethod' => [
                'id' => 'cod',
            ],
        ];

        $checkoutResponse = $this->postJson('/api/guest-checkout', $payload);
        $checkoutResponse->assertStatus(201);

        $orderNumber = $checkoutResponse->json('data.order.order_number');

        // Lookup order
        $lookupResponse = $this->getJson("/api/guest-orders/{$orderNumber}?email=alice@example.com&phone=01722222222");
        $lookupResponse->assertStatus(200)
            ->assertJsonPath('data.order.order_number', $orderNumber)
            ->assertJsonPath('data.guest.email', 'alice@example.com');

        // Track order
        $trackResponse = $this->getJson("/api/guest-orders/{$orderNumber}/track?email=alice@example.com&phone=01722222222");
        $trackResponse->assertStatus(200)
            ->assertJsonPath('data.order_number', $orderNumber);

        // Wrong email should return 404
        $failResponse = $this->getJson("/api/guest-orders/{$orderNumber}?email=wrong@example.com&phone=01722222222");
        $failResponse->assertStatus(404);
    }
}
