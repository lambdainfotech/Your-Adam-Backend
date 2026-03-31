<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductTypeService
{
    /**
     * Convert product type (simple to variable or vice versa)
     */
    public function convertProductType(Product $product, string $newType): array
    {
        if ($product->product_type === $newType) {
            return [
                'success' => false,
                'message' => 'Product is already of type: ' . $newType,
            ];
        }

        if ($newType === 'variable') {
            return $this->convertToVariable($product);
        }

        return $this->convertToSimple($product);
    }

    /**
     * Convert simple product to variable product
     */
    private function convertToVariable(Product $product): array
    {
        // Check if product has order items (can't convert if sold)
        $hasOrders = $product->variants()->whereHas('orderItems')->exists();
        
        if ($hasOrders) {
            return [
                'success' => false,
                'message' => 'Cannot convert product with existing orders to variable type.',
            ];
        }

        try {
            $product->update([
                'product_type' => 'variable',
                'has_variants' => true,
                // Keep simple product stock fields for reference
            ]);

            return [
                'success' => true,
                'message' => 'Product converted to variable type. Please generate variants.',
                'next_step' => 'generate_variants',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Conversion failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Convert variable product to simple product
     */
    private function convertToSimple(Product $product): array
    {
        // Check if has active variants with orders
        $hasActiveOrders = $product->variants()->whereHas('orderItems')->exists();

        if ($hasActiveOrders) {
            return [
                'success' => false,
                'message' => 'Cannot convert product with variant orders to simple type.',
            ];
        }

        try {
            // Calculate total stock from all variants
            $totalStock = $product->variants->sum('stock_quantity');

            // Get price range
            $prices = $product->variants->pluck('price')->filter();
            $basePrice = $prices->isNotEmpty() ? $prices->min() : $product->base_price;

            $product->update([
                'product_type' => 'simple',
                'has_variants' => false,
                'base_price' => $basePrice,
                'stock_quantity' => $totalStock,
                'stock_status' => $totalStock > 0 ? 'in_stock' : 'out_of_stock',
            ]);

            // Optionally delete variants (soft delete)
            // $product->variants()->delete();

            return [
                'success' => true,
                'message' => 'Product converted to simple type.',
                'stock_merged' => $totalStock,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Conversion failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate product data based on type
     */
    public function validateProductData(array $data, ?string $productType = null): array
    {
        $type = $productType ?? ($data['product_type'] ?? 'simple');

        $commonRules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
        ];

        $simpleRules = [
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'stock_quantity' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ];

        $variableRules = [
            'sku_prefix' => 'required|string|max:20',
        ];

        $rules = array_merge(
            $commonRules,
            $type === 'simple' ? $simpleRules : $variableRules
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get product type configuration
     */
    public function getTypeConfig(string $type): array
    {
        $configs = [
            'simple' => [
                'name' => 'Simple Product',
                'description' => 'A standalone product with no variations',
                'features' => [
                    'single_sku' => true,
                    'direct_stock' => true,
                    'single_price' => true,
                ],
                'stock_managed_at' => 'product',
            ],
            'variable' => [
                'name' => 'Variable Product',
                'description' => 'A product with multiple variations like size, color',
                'features' => [
                    'multiple_skus' => true,
                    'variant_stock' => true,
                    'variant_prices' => true,
                ],
                'stock_managed_at' => 'variant',
            ],
        ];

        return $configs[$type] ?? $configs['simple'];
    }

    /**
     * Determine if product should be simple or variable based on attributes
     */
    public function suggestProductType(array $attributes): string
    {
        $hasVariationAttributes = collect($attributes)->contains(function ($attr) {
            return $attr['is_variation'] ?? false;
        });

        return $hasVariationAttributes ? 'variable' : 'simple';
    }

    /**
     * Sync product attributes based on type
     */
    public function syncAttributes(Product $product, array $attributeIds): void
    {
        // Delete existing
        $product->productAttributes()->delete();

        // Insert new
        if (!empty($attributeIds)) {
            $data = [];
            foreach ($attributeIds as $attributeId) {
                $data[] = [
                    'product_id' => $product->id,
                    'attribute_id' => $attributeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('product_attributes')->insert($data);
        }

        // Update has_variants flag if variable type
        if ($product->product_type === 'variable' && !empty($attributeIds)) {
            $product->update(['has_variants' => true]);
        }
    }
}
