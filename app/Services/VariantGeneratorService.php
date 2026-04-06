<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariantGeneratorService
{
    protected CodeGeneratorService $codeGenerator;
    
    public function __construct(CodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }
    
    /**
     * Generate all possible variant combinations from selected attributes
     */
    public function generateVariants(
        Product $product,
        array $attributeData,
        array $options = []
    ): array {
        $defaultOptions = [
            'base_price' => $product->base_price,
            'price_adjustment' => [], // [attribute_value_id => adjustment_amount]
            'default_stock' => 0,
            'default_sku_pattern' => '{prefix}-{attributes}',
        ];
        $options = array_merge($defaultOptions, $options);

        // Generate all combinations
        $combinations = $this->generateCombinations($attributeData);
        
        $results = [
            'created' => [],
            'existing' => [],
            'failed' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($combinations as $index => $combination) {
                // Get attribute value IDs and details
                $attributeValueIds = [];
                $attributeCodes = [];
                $priceAdjustment = 0;

                foreach ($combination as $attributeId => $valueId) {
                    $attributeValueIds[] = $valueId;
                    
                    $value = AttributeValue::with('attribute')->find($valueId);
                    if ($value) {
                        $attributeCodes[] = Str::upper(Str::slug($value->value));
                        
                        // Add price adjustment if specified
                        if (isset($options['price_adjustment'][$valueId])) {
                            $priceAdjustment += $options['price_adjustment'][$valueId];
                        }
                    }
                }

                // Get attribute values for SKU generation
                $attributeValues = [];
                foreach ($combination as $attributeId => $valueId) {
                    $value = AttributeValue::find($valueId);
                    if ($value) {
                        $attributeValues[] = $value->value;
                    }
                }
                
                // Ensure SKU prefix exists
                if (empty($product->sku_prefix)) {
                    $category = \App\Models\Category::find($product->category_id);
                    $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : 'VAR';
                    $product->update(['sku_prefix' => $categoryCode]);
                    $product->refresh();
                }
                
                // Generate SKU using CodeGeneratorService
                $sku = $this->codeGenerator->generateVariantSku(
                    $product,
                    $attributeValues,
                    $index
                );
                
                // Generate related barcode for variant
                $barcode = $this->codeGenerator->generateVariantBarcode($product, $index);

                // Check if variant already exists with these exact attributes
                $existingVariant = $this->findExistingVariant($product->id, $attributeValueIds);

                if ($existingVariant) {
                    $results['existing'][] = [
                        'id' => $existingVariant->id,
                        'sku' => $existingVariant->sku,
                        'attributes' => $attributeCodes,
                    ];
                    continue;
                }

                // Create new variant
                $variantPrice = $options['base_price'] + $priceAdjustment;

                $variant = Variant::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'price' => $variantPrice > 0 ? $variantPrice : $options['base_price'],
                    'stock_quantity' => $options['default_stock'],
                    'stock_status' => $options['default_stock'] > 0 ? 'in_stock' : 'out_of_stock',
                    'low_stock_threshold' => $product->low_stock_threshold ?? 5,
                    'manage_stock' => true,
                    'is_active' => true,
                    'position' => $index,
                ]);

                // Attach attribute values
                $variant->attributeValues()->attach($attributeValueIds);

                $results['created'][] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock' => $variant->stock_quantity,
                    'attributes' => $attributeCodes,
                ];
            }

            // Update product has_variants flag
            $product->update(['has_variants' => true]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Generate all combinations of attribute values
     */
    private function generateCombinations(array $attributeData): array
    {
        $arrays = [];
        
        foreach ($attributeData as $attributeId => $valueIds) {
            if (!empty($valueIds)) {
                $arrays[$attributeId] = $valueIds;
            }
        }

        if (empty($arrays)) {
            return [];
        }

        return $this->cartesianProduct($arrays);
    }

    /**
     * Calculate cartesian product of arrays
     */
    private function cartesianProduct(array $input): array
    {
        $result = [[]];

        foreach ($input as $key => $values) {
            $append = [];

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }



    /**
     * Find existing variant with exact same attribute values
     */
    private function findExistingVariant(int $productId, array $attributeValueIds): ?Variant
    {
        // Sort for consistent comparison
        sort($attributeValueIds);

        $variants = Variant::where('product_id', $productId)
            ->with('attributeValues')
            ->get();

        foreach ($variants as $variant) {
            $variantValueIds = $variant->attributeValues->pluck('id')->toArray();
            sort($variantValueIds);

            if ($variantValueIds === $attributeValueIds) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * Duplicate variant with modifications
     */
    public function duplicateVariant(Variant $sourceVariant, array $overrides = []): Variant
    {
        $newVariant = $sourceVariant->replicate();
        
        // Apply overrides
        foreach ($overrides as $key => $value) {
            $newVariant->$key = $value;
        }

        // Generate unique SKU
        $baseSku = $newVariant->sku ?: $sourceVariant->sku . '-COPY';
        $newVariant->sku = $this->makeUniqueSku($baseSku);

        // Reset stock
        $newVariant->stock_quantity = $overrides['stock_quantity'] ?? 0;
        $newVariant->stock_status = 'out_of_stock';
        
        $newVariant->save();

        // Copy attribute values
        $attributeValueIds = $sourceVariant->attributeValues->pluck('id')->toArray();
        $newVariant->attributeValues()->attach($attributeValueIds);

        return $newVariant;
    }

    /**
     * Make SKU unique
     */
    private function makeUniqueSku(string $baseSku): string
    {
        $sku = $baseSku;
        $counter = 1;

        while (Variant::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Delete variant with checks
     */
    public function deleteVariant(Variant $variant): array
    {
        // Check if variant is used in orders
        $orderCount = $variant->orderItems()->count();
        
        if ($orderCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete variant. It is used in {$orderCount} order(s).",
                'orders' => $orderCount,
            ];
        }

        // Check if variant is in carts
        $cartCount = $variant->cartItems()->count();
        
        DB::beginTransaction();
        
        try {
            // Remove attribute value associations
            $variant->attributeValues()->detach();
            
            // Delete variant images
            $variant->images()->delete();
            
            // Delete variant
            $variant->delete();
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Variant deleted successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete variant: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reorder variants
     */
    public function reorderVariants(Product $product, array $variantOrder): void
    {
        foreach ($variantOrder as $index => $variantId) {
            Variant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->update(['position' => $index]);
        }
    }

    /**
     * Toggle variant status
     */
    public function toggleVariantStatus(Variant $variant): bool
    {
        $variant->is_active = !$variant->is_active;
        return $variant->save();
    }

    /**
     * Get variant combinations preview (before generation)
     */
    public function getCombinationsPreview(array $attributeData): array
    {
        $combinations = $this->generateCombinations($attributeData);
        $preview = [];

        foreach ($combinations as $combination) {
            $attributes = [];
            foreach ($combination as $attributeId => $valueId) {
                $value = AttributeValue::with('attribute')->find($valueId);
                if ($value) {
                    $attributes[] = [
                        'attribute' => $value->attribute->name,
                        'value' => $value->value,
                    ];
                }
            }
            $preview[] = $attributes;
        }

        return [
            'count' => count($preview),
            'combinations' => $preview,
        ];
    }
}
