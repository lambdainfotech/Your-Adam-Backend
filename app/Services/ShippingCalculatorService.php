<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Cart;
use App\Models\Address;

class ShippingCalculatorService
{
    /**
     * Calculate shipping cost
     */
    public function calculateShipping(array $items, ?int $addressId = null, ?string $city = null): array
    {
        $settings = Setting::allSettings();
        
        // Get settings
        $freeShippingThreshold = (float) ($settings['feature_free_shipping_threshold'] ?? 2000);
        $baseShippingRate = (float) ($settings['shipping_base_rate'] ?? 100);
        $perItemRate = (float) ($settings['shipping_per_item_rate'] ?? 50);
        $expressShippingRate = (float) ($settings['shipping_express_rate'] ?? 200);
        
        // Calculate subtotal
        $subtotal = 0;
        $totalWeight = 0;
        $totalItems = 0;
        
        foreach ($items as $item) {
            $price = $item['sale_price'] ?? $item['base_price'] ?? $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $weight = $item['weight'] ?? 0.5; // Default 0.5kg
            
            $subtotal += $price * $quantity;
            $totalWeight += $weight * $quantity;
            $totalItems += $quantity;
        }
        
        // Check for free shipping
        $isFreeShipping = $subtotal >= $freeShippingThreshold;
        
        // Calculate standard shipping
        $standardShipping = $isFreeShipping ? 0 : ($baseShippingRate + ($perItemRate * $totalItems));
        
        // City-based adjustments
        $cityMultiplier = $this->getCityMultiplier($city);
        $standardShipping *= $cityMultiplier;
        
        // Express shipping (1.5x standard)
        $expressShipping = $isFreeShipping ? $expressShippingRate : ($standardShipping + $expressShippingRate);
        
        return [
            'subtotal' => round($subtotal, 2),
            'freeShippingThreshold' => $freeShippingThreshold,
            'isFreeShippingEligible' => $isFreeShipping,
            'options' => [
                [
                    'id' => 'standard',
                    'name' => 'Standard Delivery',
                    'description' => 'Delivery in 3-5 business days',
                    'cost' => round($standardShipping, 2),
                    'estimatedDays' => '3-5',
                    'isFree' => $isFreeShipping,
                ],
                [
                    'id' => 'express',
                    'name' => 'Express Delivery',
                    'description' => 'Delivery in 1-2 business days',
                    'cost' => round($expressShipping, 2),
                    'estimatedDays' => '1-2',
                    'isFree' => false,
                ],
            ],
            'summary' => [
                'totalItems' => $totalItems,
                'totalWeight' => round($totalWeight, 2) . ' kg',
            ],
        ];
    }

    /**
     * Get city multiplier for shipping cost
     */
    private function getCityMultiplier(?string $city): float
    {
        if (!$city) {
            return 1.0;
        }
        
        $city = strtolower($city);
        
        // Dhaka and major cities
        if (in_array($city, ['dhaka'])) {
            return 1.0;
        }
        
        // Divisional cities
        if (in_array($city, ['chittagong', 'chattogram', 'sylhet', 'rajshahi', 'khulna', 'barisal', 'rangpur', 'mymensingh'])) {
            return 1.2;
        }
        
        // Other cities
        return 1.5;
    }

    /**
     * Get available shipping methods
     */
    public function getShippingMethods(): array
    {
        $settings = Setting::allSettings();
        $freeShippingThreshold = (float) ($settings['feature_free_shipping_threshold'] ?? 2000);
        
        return [
            [
                'id' => 'standard',
                'name' => 'Standard Delivery',
                'description' => '3-5 business days',
                'baseRate' => (float) ($settings['shipping_base_rate'] ?? 100),
                'freeThreshold' => $freeShippingThreshold,
            ],
            [
                'id' => 'express',
                'name' => 'Express Delivery',
                'description' => '1-2 business days',
                'baseRate' => (float) ($settings['shipping_express_rate'] ?? 200),
                'freeThreshold' => null,
            ],
            [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive',
                'baseRate' => (float) ($settings['shipping_cod_rate'] ?? 50),
                'freeThreshold' => $freeShippingThreshold,
            ],
        ];
    }
}
