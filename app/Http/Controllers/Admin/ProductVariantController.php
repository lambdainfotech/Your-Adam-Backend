<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Services\VariantGeneratorService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    protected VariantGeneratorService $variantGenerator;
    protected PricingService $pricingService;

    public function __construct(VariantGeneratorService $variantGenerator, PricingService $pricingService)
    {
        $this->variantGenerator = $variantGenerator;
        $this->pricingService = $pricingService;
    }

    /**
     * Show variant management page for a product
     */
    public function index(Product $product)
    {
        $product->load(['variants.attributeValues.attribute', 'variants.mainImage', 'productAttributes.attribute']);
        
        // Get all attributes that can be used for variations
        $attributes = DB::table('attributes')
            ->where('is_variation', true)
            ->orderBy('sort_order')
            ->get();

        // Get attribute values for each attribute
        foreach ($attributes as $attribute) {
            $attribute->values = DB::table('attribute_values')
                ->where('attribute_id', $attribute->id)
                ->orderBy('sort_order')
                ->get();
        }

        // Get current product attributes
        $productAttributeIds = DB::table('product_attributes')
            ->where('product_id', $product->id)
            ->pluck('attribute_id')
            ->toArray();

        // Calculate price range
        $priceRange = $this->pricingService->getVariableProductPriceRange($product);

        return view('admin.products.variants', compact(
            'product', 
            'attributes', 
            'productAttributeIds',
            'priceRange'
        ));
    }

    /**
     * Generate variants based on selected attributes
     */
    public function generate(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attributes' => 'required|array|min:1',
            'attributes.*' => 'exists:attributes,id',
            'base_price' => 'nullable|numeric|min:0',
            'default_stock' => 'nullable|integer|min:0',
            'price_adjustments' => 'nullable|array',
        ]);

        // Get selected attribute values
        $attributeData = [];
        foreach ($validated['attributes'] as $attributeId) {
            $values = $request->input('attribute_values_' . $attributeId, []);
            if (!empty($values)) {
                $attributeData[$attributeId] = $values;
            }
        }

        if (empty($attributeData)) {
            return redirect()->back()
                ->with('error', 'Please select at least one value for each attribute.');
        }

        // Update product attributes
        DB::table('product_attributes')
            ->where('product_id', $product->id)
            ->delete();

        foreach ($validated['attributes'] as $attributeId) {
            DB::table('product_attributes')->insert([
                'product_id' => $product->id,
                'attribute_id' => $attributeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate options
        $options = [
            'base_price' => $validated['base_price'] ?? $product->base_price,
            'default_stock' => $validated['default_stock'] ?? 0,
            'price_adjustment' => $validated['price_adjustments'] ?? [],
        ];

        // Generate variants
        $results = $this->variantGenerator->generateVariants($product, $attributeData, $options);

        $message = sprintf(
            'Generated %d new variant(s). %d already existed.',
            count($results['created']),
            count($results['existing'])
        );

        return redirect()->route('admin.products.variants', $product)
            ->with('success', $message);
    }

    /**
     * Preview variant combinations before generation
     */
    public function previewCombinations(Request $request, Product $product)
    {
        $attributeData = [];
        
        foreach ($request->input('attributes', []) as $attributeId) {
            $values = $request->input('attribute_values_' . $attributeId, []);
            if (!empty($values)) {
                $attributeData[$attributeId] = $values;
            }
        }

        $preview = $this->variantGenerator->getCombinationsPreview($attributeData);

        return response()->json($preview);
    }

    /**
     * Get variant data for editing (AJAX)
     */
    public function getVariant($variantId)
    {
        $variant = Variant::with(['attributeValues.attribute', 'product', 'mainImage'])->find($variantId);

        if (!$variant) {
            return response()->json(['error' => 'Variant not found'], 404);
        }

        return response()->json([
            'variant' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'price' => $variant->price,
                'discount_type' => $variant->discount_type,
                'discount_value' => $variant->discount_value,
                'sale_price' => $variant->sale_price,
                'cost_price' => $variant->cost_price,
                'stock_quantity' => $variant->stock_quantity,
                'stock_status' => $variant->stock_status,
                'manage_stock' => $variant->manage_stock,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'weight' => $variant->weight,
                'is_active' => $variant->is_active,
                'final_price' => $variant->final_price,
            ],
            'attributes' => $variant->attributeValues->map(fn($av) => [
                'attribute_name' => $av->attribute->name,
                'value' => $av->value,
            ]),
            'image' => $variant->mainImage ? $variant->mainImage->full_image_url : null,
        ]);
    }

    /**
     * Update variant details
     */
    public function updateVariant(Request $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:variants,sku,' . $variantId,
            'barcode' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,on_backorder',
            'manage_stock' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $oldStock = $variant->stock_quantity;
        $newStock = $validated['stock_quantity'];

        $validated['manage_stock'] = $request->boolean('manage_stock', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Calculate sale price based on discount
        $basePrice = (float) ($validated['price'] ?? $variant->product->base_price ?? 0);
        $validated['sale_price'] = $this->pricingService->calculateSalePrice(
            $basePrice,
            $validated['discount_type'] ?? null,
            isset($validated['discount_value']) ? (float) $validated['discount_value'] : null
        );

        // Auto-update stock status if manage_stock is enabled
        if ($validated['manage_stock'] && $newStock !== $oldStock) {
            $validated['stock_status'] = $newStock > 0 ? 'in_stock' : 'out_of_stock';
        }

        $variant->update($validated);

        // Log stock change
        if ($newStock !== $oldStock) {
            \App\Models\InventoryMovement::create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'movement_type' => $newStock > $oldStock ? 'in' : 'out',
                'quantity' => abs($newStock - $oldStock),
                'reason' => 'Manual stock update',
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'created_by' => auth()->id(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully.',
                'variant' => $variant->fresh(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Quick update variant (inline edit)
     */
    public function quickUpdate(Request $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $validated = $request->validate([
            'field' => 'required|in:price,stock_quantity,is_active',
            'value' => 'required',
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        switch ($field) {
            case 'price':
                $variant->price = (float) $value;
                break;
            case 'stock_quantity':
                $oldStock = $variant->stock_quantity;
                $variant->stock_quantity = (int) $value;
                if ($variant->manage_stock) {
                    $variant->stock_status = $variant->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
                }
                
                // Log stock change
                if ($variant->stock_quantity !== $oldStock) {
                    \App\Models\InventoryMovement::create([
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'movement_type' => $variant->stock_quantity > $oldStock ? 'in' : 'out',
                        'quantity' => abs($variant->stock_quantity - $oldStock),
                        'reason' => 'Quick inline update',
                        'stock_before' => $oldStock,
                        'stock_after' => $variant->stock_quantity,
                        'created_by' => auth()->id(),
                    ]);
                }
                break;
            case 'is_active':
                $variant->is_active = (bool) $value;
                break;
        }

        $variant->save();

        return response()->json([
            'success' => true,
            'variant' => [
                'id' => $variant->id,
                'price' => $variant->price,
                'stock_quantity' => $variant->stock_quantity,
                'stock_status' => $variant->stock_status,
                'is_active' => $variant->is_active,
                'final_price' => $variant->final_price,
            ],
        ]);
    }

    /**
     * Delete a variant
     */
    public function deleteVariant($variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $result = $this->variantGenerator->deleteVariant($variant);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Update product attributes
     */
    public function updateAttributes(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:attributes,id',
        ]);

        // Delete existing product attributes
        DB::table('product_attributes')
            ->where('product_id', $product->id)
            ->delete();

        // Insert new product attributes
        if (!empty($validated['attributes'])) {
            $data = [];
            foreach ($validated['attributes'] as $attributeId) {
                $data[] = [
                    'product_id' => $product->id,
                    'attribute_id' => $attributeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('product_attributes')->insert($data);
        }

        return redirect()->back()
            ->with('success', 'Product attributes updated successfully.');
    }

    /**
     * Toggle variant status
     */
    public function toggleStatus($variantId)
    {
        $variant = Variant::findOrFail($variantId);
        $variant->is_active = !$variant->is_active;
        $variant->save();

        return response()->json([
            'success' => true,
            'is_active' => $variant->is_active,
        ]);
    }

    /**
     * Reorder variants
     */
    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variants' => 'required|array',
            'variants.*' => 'exists:variants,id',
        ]);

        $this->variantGenerator->reorderVariants($product, $validated['variants']);

        return response()->json(['success' => true]);
    }

    /**
     * Add single variant manually
     */
    public function addVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:50|unique:variants',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'exists:attribute_values,id',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        // Check if combination already exists
        $existing = $product->variants->first(function ($variant) use ($validated) {
            $existingIds = $variant->attributeValues->pluck('id')->sort()->values()->toArray();
            $newIds = collect($validated['attribute_values'])->sort()->values()->toArray();
            return $existingIds === $newIds;
        });

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A variant with these attributes already exists.',
            ], 422);
        }

        // Get attribute values for SKU generation
        $attributeValues = \App\Models\AttributeValue::whereIn('id', $validated['attribute_values'])
            ->get()
            ->pluck('value')
            ->toArray();

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            // Ensure SKU prefix exists
            if (empty($product->sku_prefix)) {
                $category = \App\Models\Category::find($product->category_id);
                $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : 'VAR';
                $product->update(['sku_prefix' => $categoryCode]);
                $product->refresh();
            }
            
            $codeGenerator = app(\App\Services\CodeGeneratorService::class);
            $validated['sku'] = $codeGenerator->generateVariantSku(
                $product,
                $attributeValues,
                $product->variants()->count()
            );
        }

        // Auto-generate related barcode for variant
        $codeGenerator = app(\App\Services\CodeGeneratorService::class);
        $barcode = $codeGenerator->generateVariantBarcode($product, $product->variants()->count());

        $variant = Variant::create([
            'product_id' => $product->id,
            'sku' => $validated['sku'],
            'barcode' => $barcode,
            'price' => $validated['price'] ?? $product->base_price,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'stock_status' => ($validated['stock_quantity'] ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
            'is_active' => true,
            'position' => $product->variants()->count(),
        ]);

        $variant->attributeValues()->attach($validated['attribute_values']);

        return response()->json([
            'success' => true,
            'variant' => $variant->load('attributeValues'),
        ]);
    }

    /**
     * Update variant image
     */
    public function updateImage(Request $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Delete old variant image if exists
        $variant->images()->delete();

        // Upload new image
        $imageService = app(\App\Services\ImageUploadService::class);
        $images = $imageService->uploadImages($variant->product_id, [$request->file('image')]);

        if (!empty($images)) {
            $image = $images[0];
            $image->variant_id = $variant->id;
            $image->is_main = true;
            $image->save();
        }

        return response()->json([
            'success' => true,
            'image_url' => $image->full_image_url ?? null,
        ]);
    }
}
