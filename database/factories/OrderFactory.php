<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = \App\Models\Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 10000);
        $shipping = 100;
        $total = $subtotal + $shipping;

        return [
            'user_id' => null,
            'guest_id' => null,
            'customer_type' => 'guest',
            'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(fake()->bothify('???###')),
            'status' => fake()->randomElement(['pending', 'confirmed', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => fake()->randomElement(['cod', 'aamarpay']),
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'coupon_code' => null,
            'coupon_discount' => 0,
            'tax_amount' => 0,
            'shipping_amount' => $shipping,
            'total_amount' => $total,
            'currency' => 'BDT',
            'notes' => null,
            'admin_notes' => null,
            'delivery_address' => [
                'name' => fake()->name(),
                'phone' => '01' . fake()->numberBetween(100000000, 999999999),
                'address' => fake()->address(),
                'city' => fake()->city(),
                'postcode' => fake()->postcode(),
            ],
            'billing_address' => null,
            'estimated_delivery_date' => fake()->optional()->date(),
            'delivered_at' => null,
            'transaction_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
