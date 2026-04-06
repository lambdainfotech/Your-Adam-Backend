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
     * Format price with currency (BDT)
     */
    public function formatPrice(float $price, string $currency = '৳'): string
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

    /**
     * Calculate sale price based on discount
     * 
     * @param float $basePrice
     * @param string|null $discountType 'percentage' or 'flat'
     * @param float|null $discountValue
     * @return float
     */
    public function calculateSalePrice(float $basePrice, ?string $discountType, ?float $discountValue): float
    {
        if (!$discountValue || $discountValue <= 0) {
            return $basePrice;
        }

        $salePrice = $basePrice;

        if ($discountType === 'percentage') {
            // Limit percentage to 1-99%
            $percentage = max(1, min(99, $discountValue));
            $salePrice = $basePrice - ($basePrice * $percentage / 100);
        } elseif ($discountType === 'flat') {
            // Ensure flat discount is less than base price
            $flatDiscount = min($discountValue, $basePrice - 0.01);
            $salePrice = $basePrice - $flatDiscount;
        }

        // Ensure sale price is not negative and less than base price
        return max(0, min($salePrice, $basePrice));
    }

    /**
     * Calculate discount amount
     * 
     * @param float $basePrice
     * @param string|null $discountType
     * @param float|null $discountValue
     * @return float
     */
    public function calculateDiscountAmount(float $basePrice, ?string $discountType, ?float $discountValue): float
    {
        if (!$discountValue || $discountValue <= 0 || $basePrice <= 0) {
            return 0;
        }

        if ($discountType === 'percentage') {
            return $basePrice * ($discountValue / 100);
        } elseif ($discountType === 'flat') {
            return min($discountValue, $basePrice);
        }

        return 0;
    }

    /**
     * Validate discount
     * 
     * @param string $type
     * @param float $value
     * @param float $basePrice
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateDiscount(string $type, float $value, float $basePrice): array
    {
        if ($value <= 0) {
            return ['valid' => false, 'message' => 'Discount value must be greater than 0'];
        }

        if ($type === 'percentage') {
            if ($value > 99) {
                return ['valid' => false, 'message' => 'Percentage discount cannot exceed 99%'];
            }
            if ($value < 1) {
                return ['valid' => false, 'message' => 'Percentage discount must be at least 1%'];
            }
        } elseif ($type === 'flat') {
            if ($value >= $basePrice) {
                return ['valid' => false, 'message' => 'Flat discount must be less than regular price'];
            }
        } else {
            return ['valid' => false, 'message' => 'Invalid discount type'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Get discount display text
     * 
     * @param string|null $discountType
     * @param float|null $discountValue
     * @return string
     */
    public function getDiscountDisplay(?string $discountType, ?float $discountValue): string
    {
        if (!$discountValue || $discountValue <= 0) {
            return '';
        }

        if ($discountType === 'percentage') {
            return '-' . number_format($discountValue, 0) . '%';
        } elseif ($discountType === 'flat') {
            return '-$' . number_format($discountValue, 2);
        }

        return '';
    }

    /**
     * Get savings display text
     * 
     * @param float $basePrice
     * @param float $salePrice
     * @return string
     */
    public function getSavingsDisplay(float $basePrice, float $salePrice): string
    {
        $savings = $basePrice - $salePrice;
        if ($savings <= 0) {
            return '';
        }
        return 'Save $' . number_format($savings, 2);
    }
}
