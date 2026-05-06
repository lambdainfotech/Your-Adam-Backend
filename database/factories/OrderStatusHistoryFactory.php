<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    protected $model = \App\Models\OrderStatusHistory::class;

    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'status' => fake()->randomElement(['pending', 'confirmed', 'packed', 'shipped', 'delivered']),
            'previous_status' => fake()->optional()->randomElement(['pending', 'confirmed', 'packed', 'shipped']),
            'notes' => fake()->optional()->sentence(),
            'changed_by' => null,
        ];
    }
}
