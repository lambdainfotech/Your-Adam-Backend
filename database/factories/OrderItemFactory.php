<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = \App\Models\OrderItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 100, 5000);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'order_id' => \App\Models\Order::factory(),
            'variant_id' => null,
            'product_name' => fake()->words(3, true),
            'variant_sku' => fake()->bothify('SKU-###???'),
            'variant_attributes' => ['color' => fake()->colorName(), 'size' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'original_price' => $unitPrice,
            'discount_amount' => 0,
            'total_price' => round($unitPrice * $quantity, 2),
        ];
    }
}
