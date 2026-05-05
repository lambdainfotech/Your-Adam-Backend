<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VariantFactory extends Factory
{
    protected $model = \App\Models\Variant::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'sku' => fake()->unique()->ean8(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock_quantity' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
