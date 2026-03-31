<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate final price for a product considering sale schedule
     */
    public function calculateProductPrice(Product $product): float
    {
        if ($product->is_on_sale && $product->sale_price !== null) {
            return (float) $product->sale_price;
        }
        return (float) ($product->base_price ?? 0);
    }

    /**
     * Calculate final price for a variant
     */
    public function calculateVariantPrice(Variant $variant): float
    {
        // If variant has its own price, use it
        if ($variant->price !== null && $variant->price > 0) {
            return (float) $variant->price;
        }
        
        // Otherwise use product's final price
        return $this->calculateProductPrice($variant->product);
    }

    /**
     * Check if product is on sale
     */
    public function isProductOnSale(Product $product): bool
    {
        if ($product->sale_price === null) {
            return false;
        }

        $now = Carbon::now();

        // Check sale start date
        if ($product->sale_start_date && $now < $product->sale_start_date) {
            return false;
        }

        // Check sale end date
        if ($product->sale_end_date && $now > $product->sale_end_date) {
            return false;
        }

        return true;
    }

    /**
     * Get sale schedule information
     */
    public function getSaleSchedule(Product $product): array
    {
        if ($product->sale_price === null) {
            return [
                'is_on_sale' => false,
                'sale_price' => null,
                'regular_price' => $product->base_price,
                'start_date' => null,
                'end_date' => null,
                'days_remaining' => 0,
            ];
        }

        $now = Carbon::now();
        $isOnSale = $this->isProductOnSale($product);
        $daysRemaining = 0;

        if ($isOnSale && $product->sale_end_date) {
            $daysRemaining = $now->diffInDays($product->sale_end_date, false);
        }

        return [
            'is_on_sale' => $isOnSale,
            'sale_price' => $product->sale_price,
            'regular_price' => $product->base_price,
            'start_date' => $product->sale_start_date?->toDateTimeString(),
            'end_date' => $product->sale_end_date?->toDateTimeString(),
            'days_remaining' => max(0, (int) $daysRemaining),
        ];
    }

    /**
     * Get price range for variable product
     */
    public function getVariableProductPriceRange(Product $product): array
    {
        if ($product->product_type !== 'variable' || $product->variants->isEmpty()) {
            return [
                'min' => $this->calculateProductPrice($product),
                'max' => $this->calculateProductPrice($product),
                'has_range' => false,
            ];
        }

        $prices = $product->variants->map(function ($variant) {
            return $this->calculateVariantPrice($variant);
        });

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
            'has_range' => $prices->min() !== $prices->max(),
        ];
    }

    /**
     * Format price with currency
     */
    public function formatPrice(float $price, string $currency = '$'): string
    {
        return $currency . number_format($price, 2);
    }

    /**
     * Calculate profit margin
     */
    public function calculateProfitMargin(float $sellingPrice, ?float $costPrice): ?float
    {
        if ($costPrice === null || $costPrice <= 0) {
            return null;
        }

        $profit = $sellingPrice - $costPrice;
        return round(($profit / $sellingPrice) * 100, 2);
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdatePrices(array $variantIds, string $operation, float $value, ?string $operationType = 'fixed'): int
    {
        $updated = 0;
        $variants = Variant::whereIn('id', $variantIds)->get();

        foreach ($variants as $variant) {
            $currentPrice = $variant->price ?: $variant->product->base_price;
            $newPrice = $currentPrice;

            switch ($operation) {
                case 'increase':
                    if ($operationType === 'percentage') {
                        $newPrice = $currentPrice * (1 + $value / 100);
                    } else {
                        $newPrice = $currentPrice + $value;
                    }
                    break;

                case 'decrease':
                    if ($operationType === 'percentage') {
                        $newPrice = $currentPrice * (1 - $value / 100);
                    } else {
                        $newPrice = max(0, $currentPrice - $value);
                    }
                    break;

                case 'set':
                    $newPrice = $value;
                    break;
            }

            $variant->price = round($newPrice, 2);
            $variant->save();
            $updated++;
        }

        return $updated;
    }
}
