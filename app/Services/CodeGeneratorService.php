<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;

class CodeGeneratorService
{
    /**
     * Generate a unique SKU for a product
     * 
     * Format: PRD-{timestamp}-{random}
     * Example: PRD-20250406-0001-A7B3
     * 
     * @param string|null $prefix Optional prefix (e.g., category code)
     * @return string
     */
    public function generateProductSku(?string $prefix = null): string
    {
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        $sequence = $this->getNextSequence('product');
        
        $sku = sprintf(
            '%s%s-%04d-%s',
            $prefix ? strtoupper($prefix) . '-' : 'PRD-',
            $date,
            $sequence,
            $random
        );
        
        // Ensure uniqueness
        while ($this->productSkuExists($sku)) {
            $random = strtoupper(substr(uniqid(), -4));
            $sequence = $this->getNextSequence('product');
            $sku = sprintf(
                '%s%s-%04d-%s',
                $prefix ? strtoupper($prefix) . '-' : 'PRD-',
                $date,
                $sequence,
                $random
            );
        }
        
        return $sku;
    }
    
    /**
     * Generate a unique barcode (EAN-13 format)
     * 
     * Format: 200000000000X (where X is check digit)
     * 200 prefix indicates internal use
     * 
     * @return string
     */
    public function generateBarcode(): string
    {
        // Generate 12 digits (200 prefix + 9 random digits)
        $prefix = '200';
        $random = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $baseCode = $prefix . $random;
        
        // Calculate EAN-13 check digit
        $checkDigit = $this->calculateEAN13CheckDigit($baseCode);
        $barcode = $baseCode . $checkDigit;
        
        // Ensure uniqueness
        while ($this->barcodeExists($barcode)) {
            $random = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $baseCode = $prefix . $random;
            $checkDigit = $this->calculateEAN13CheckDigit($baseCode);
            $barcode = $baseCode . $checkDigit;
        }
        
        return $barcode;
    }
    
    /**
     * Generate variant SKU based on product prefix and attributes
     * 
     * @param Product $product
     * @param array $attributeValues
     * @param int $index
     * @return string
     */
    public function generateVariantSku(Product $product, array $attributeValues, int $index = 0): string
    {
        $prefix = $product->sku_prefix ?: 'VAR';
        $productId = str_pad((string) $product->id, 4, '0', STR_PAD_LEFT);
        $variantIndex = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
        
        // Build attribute suffix from attribute values
        $suffix = '';
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $value) {
                // Take first 3 characters of each value, uppercase
                $suffix .= '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $value), 0, 3));
            }
        }
        
        $sku = sprintf(
            '%s-%s-%s%s',
            strtoupper($prefix),
            $productId,
            $variantIndex,
            $suffix
        );
        
        // Ensure uniqueness
        $originalSku = $sku;
        $counter = 1;
        while ($this->variantSkuExists($sku)) {
            $sku = $originalSku . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $sku;
    }
    
    /**
     * Generate a simple random SKU
     * 
     * @param string $category Optional category code
     * @return string
     */
    public function generateSimpleSku(string $category = ''): string
    {
        $prefix = $category ?: 'SKU';
        $timestamp = substr(time(), -6);
        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        
        return sprintf('%s-%s-%s', strtoupper($prefix), $timestamp, $random);
    }
    
    /**
     * Calculate EAN-13 check digit
     * 
     * @param string $barcode 12-digit barcode without check digit
     * @return int
     */
    private function calculateEAN13CheckDigit(string $barcode): int
    {
        $sum = 0;
        $length = strlen($barcode);
        
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $barcode[$i];
            // Odd positions (1, 3, 5...) multiplied by 1
            // Even positions (2, 4, 6...) multiplied by 3
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        
        $modulo = $sum % 10;
        return ($modulo === 0) ? 0 : (10 - $modulo);
    }
    
    /**
     * Check if product SKU already exists
     * 
     * @param string $sku
     * @return bool
     */
    private function productSkuExists(string $sku): bool
    {
        return Product::where('sku', $sku)->exists();
    }
    
    /**
     * Check if barcode already exists
     * 
     * @param string $barcode
     * @return bool
     */
    private function barcodeExists(string $barcode): bool
    {
        // Check in products table
        if (Product::where('barcode', $barcode)->exists()) {
            return true;
        }
        
        // Check in variants table (include soft-deleted since DB unique constraint does)
        if (Variant::withTrashed()->where('barcode', $barcode)->exists()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if variant SKU already exists
     * 
     * @param string $sku
     * @return bool
     */
    private function variantSkuExists(string $sku): bool
    {
        return Variant::withTrashed()->where('sku', $sku)->exists();
    }
    
    /**
     * Get next sequence number for the day
     * 
     * @param string $type
     * @return int
     */
    private function getNextSequence(string $type): int
    {
        // Use cache to track sequence for the day
        $cacheKey = "{$type}_sku_sequence_" . date('Ymd');
        $sequence = cache()->increment($cacheKey);
        
        // Set expiration to end of day
        if ($sequence === 1) {
            $ttl = now()->endOfDay()->diffInSeconds(now());
            cache()->put($cacheKey, 1, $ttl);
            cache()->increment($cacheKey);
        }
        
        return $sequence;
    }
    
    /**
     * Generate related barcode for variant (Strategy 2)
     * Format: 200{PRODUCT_ID}{VARIANT_SEQ}C
     * All variants of same product share the same base with different suffixes
     * 
     * @param Product $product
     * @param int $variantIndex
     * @return string
     */
    public function generateVariantBarcode(Product $product, int $variantIndex): string
    {
        // Product ID (padded to 7 digits) + variant sequence (2 digits) = 9 digits
        // Prefix '200' + 9 digits = 12 digits before check digit
        $productId = str_pad((string) $product->id, 7, '0', STR_PAD_LEFT);
        $variantSeq = str_pad((string) ($variantIndex + 1), 2, '0', STR_PAD_LEFT);
        
        // Build base code: 200 + productId(7) + variantSeq(2) = 12 digits
        $baseCode = '200' . $productId . $variantSeq;
        
        // Ensure exactly 12 digits
        $baseCode = substr($baseCode, 0, 12);
        
        // Calculate check digit
        $checkDigit = $this->calculateEAN13CheckDigit($baseCode);
        $barcode = $baseCode . $checkDigit;
        
        // Ensure uniqueness - if collision, try with random suffix
        if ($this->barcodeExists($barcode)) {
            return $this->generateUniqueVariantBarcode($product, $variantIndex);
        }
        
        return $barcode;
    }
    
    /**
     * Generate a unique variant barcode when related format collides
     * 
     * @param Product $product
     * @param int $variantIndex
     * @return string
     */
    private function generateUniqueVariantBarcode(Product $product, int $variantIndex): string
    {
        // Use product ID + random component
        $productPart = str_pad((string) $product->id, 5, '0', STR_PAD_LEFT);
        $randomPart = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $baseCode = '200' . $productPart . $randomPart;
        $checkDigit = $this->calculateEAN13CheckDigit($baseCode);
        $barcode = $baseCode . $checkDigit;
        
        // Keep trying until unique
        while ($this->barcodeExists($barcode)) {
            $randomPart = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $baseCode = '200' . $productPart . $randomPart;
            $checkDigit = $this->calculateEAN13CheckDigit($baseCode);
            $barcode = $baseCode . $checkDigit;
        }
        
        return $barcode;
    }
}
