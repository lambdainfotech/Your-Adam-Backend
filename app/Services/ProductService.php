<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\UniqueConstraintViolationException;

class ProductService
{
    public function __construct(
        protected PricingService $pricingService,
        protected CodeGeneratorService $codeGenerator,
        protected ImageUploadService $imageService,
        protected CategoryService $categoryService,
    ) {
    }

    /**
     * Create a new product from validated request data.
     */
    public function createProduct(Request $request, array $validated): Product
    {
        $validated = $this->categoryService->resolveCategoryIds($validated);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['manage_stock'] = $request->boolean('manage_stock', true);

        // Handle predefined descriptions
        if ($request->filled('predefined_description_id')) {
            $predefinedDesc = \App\Models\PredefinedDescription::find($request->predefined_description_id);
            $validated['description'] = $predefinedDesc?->content;
        }

        if ($request->filled('predefined_short_description_id')) {
            $predefinedShortDesc = \App\Models\PredefinedDescription::find($request->predefined_short_description_id);
            $validated['short_description'] = $predefinedShortDesc?->content;
        }

        // Calculate sale price based on discount
        $validated['sale_price'] = $this->pricingService->calculateSalePrice(
            (float) ($validated['base_price'] ?? 0),
            $validated['discount_type'] ?? null,
            isset($validated['discount_value']) ? (float) $validated['discount_value'] : null
        );

        // Set default stock status based on quantity
        if ($validated['product_type'] === 'simple') {
            $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
            $validated['stock_status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
        }

        $product = Product::create($validated);

        // Auto-generate SKU and Barcode for simple products if not provided
        if ($product->product_type === 'simple') {
            $this->generateSimpleProductCodes($product);
        }

        // Save product attributes
        if ($request->has('attributes')) {
            $attributeIds = $request->input('attributes', []);
            $product->variationAttributes()->sync($attributeIds);
        }

        // Create variants if provided (for variable products)
        if ($product->product_type === 'variable' && $request->has('variants')) {
            $this->createVariants($product, $request->input('variants', []));
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->imageService->uploadImages($product->id, $request->file('images'));
        }

        return $product;
    }

    /**
     * Update an existing product from validated request data.
     */
    public function updateProduct(Request $request, Product $product, array $validated): Product
    {
        $validated = $this->categoryService->resolveCategoryIds($validated);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        // Handle predefined descriptions
        if ($request->filled('predefined_description_id')) {
            $predefinedDesc = \App\Models\PredefinedDescription::find($request->predefined_description_id);
            $validated['description'] = $predefinedDesc?->content;
        }

        if ($request->filled('predefined_short_description_id')) {
            $predefinedShortDesc = \App\Models\PredefinedDescription::find($request->predefined_short_description_id);
            $validated['short_description'] = $predefinedShortDesc?->content;
        }

        // Calculate sale price based on discount
        $validated['sale_price'] = $this->pricingService->calculateSalePrice(
            (float) ($validated['base_price'] ?? 0),
            $validated['discount_type'] ?? null,
            isset($validated['discount_value']) ? (float) $validated['discount_value'] : null
        );

        if ($product->product_type === 'simple') {
            $validated['manage_stock'] = $request->boolean('manage_stock', true);

            // Auto-update stock status if stock quantity changed
            if (isset($validated['stock_quantity'])) {
                $validated['stock_status'] = $validated['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
            }
        }

        $product->update($validated);

        // Auto-generate SKU and Barcode for simple products if not provided
        if ($product->product_type === 'simple') {
            $this->generateSimpleProductCodes($product);
        }

        // Update product attributes
        if ($request->has('attributes')) {
            $attributeIds = $request->input('attributes', []);
            $product->variationAttributes()->sync($attributeIds);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->imageService->uploadImages($product->id, $request->file('images'));
        }

        return $product;
    }

    /**
     * Auto-generate SKU and Barcode for simple products if not provided.
     */
    protected function generateSimpleProductCodes(Product $product): void
    {
        $updateData = [];

        if (empty($product->sku)) {
            $category = Category::find($product->category_id);
            $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : null;
            $updateData['sku'] = $this->codeGenerator->generateProductSku($categoryCode . '-PRD');
        }

        if (empty($product->barcode)) {
            $updateData['barcode'] = $this->codeGenerator->generateBarcode();
        }

        if (!empty($updateData)) {
            $product->update($updateData);
        }
    }

    /**
     * Create variants for a variable product.
     */
    public function createVariants(Product $product, array $variantsData): void
    {
        $position = 0;

        // Ensure SKU prefix exists
        if (empty($product->sku_prefix)) {
            $category = Category::find($product->category_id);
            $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : 'VAR';
            $product->update(['sku_prefix' => $categoryCode]);
            $product->refresh();
        }

        foreach ($variantsData as $variantData) {
            if (($variantData['stock_quantity'] ?? 0) <= 0) {
                continue;
            }

            $sku = $variantData['sku'] ?? null;
            $barcode = $variantData['barcode'] ?? null;
            $attributeValues = [];

            if (!empty($variantData['attribute_values'])) {
                $valueIds = explode(',', $variantData['attribute_values']);
                $values = AttributeValue::whereIn('id', $valueIds)->get();
                $attributeValues = $values->pluck('value')->toArray();
            }

            if (empty($sku)) {
                $sku = $this->codeGenerator->generateVariantSku($product, $attributeValues, $position);
            }

            if (empty($barcode)) {
                $barcode = $this->codeGenerator->generateVariantBarcode($product, $position);
            }

            $variant = $this->createVariantWithRetry($product, [
                'sku' => $sku,
                'barcode' => $barcode,
                'price' => $variantData['price'] ?? $product->base_price,
                'wholesale_percentage' => $variantData['wholesale_percentage'] ?? null,
                'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                'stock_status' => ($variantData['stock_quantity'] ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
                'is_active' => $variantData['is_active'] ?? true,
                'position' => $position,
            ]);

            if (!empty($variantData['attribute_values'])) {
                $valueIds = explode(',', $variantData['attribute_values']);
                $variant->attributeValues()->attach($valueIds);
            }

            $position++;
        }

        $product->update(['has_variants' => true]);
    }

    /**
     * Create a variant with retry on unique SKU constraint.
     */
    protected function createVariantWithRetry(Product $product, array $data): Variant
    {
        try {
            return $product->variants()->create($data);
        } catch (UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'variants_sku_unique')) {
                $data['sku'] = $this->makeUniqueVariantSku($data['sku']);
                return $product->variants()->create($data);
            }
            throw $e;
        }
    }

    /**
     * Make variant SKU unique by appending a counter.
     */
    protected function makeUniqueVariantSku(string $baseSku): string
    {
        $sku = $baseSku;
        $counter = 1;

        while (Variant::where('sku', $sku)->exists()) {
            $sku = $baseSku . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $sku;
    }

    /**
     * Duplicate a product including its variants.
     */
    public function duplicateProduct(Product $product): Product
    {
        $newProduct = $product->replicate();
        $newProduct->slug = $product->slug . '-copy-' . time();
        $newProduct->sku = $product->sku ? $product->sku . '-COPY' : null;
        $newProduct->sku_prefix = $product->sku_prefix ? $product->sku_prefix . '-COPY' : null;
        $newProduct->is_active = false;
        $newProduct->save();

        if ($product->product_type === 'variable') {
            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = $variant->sku . '-COPY';
                $newVariant->stock_quantity = 0;
                $newVariant->stock_status = 'out_of_stock';
                $newVariant->save();

                $newVariant->attributeValues()->attach($variant->attributeValues->pluck('id'));
            }
        }

        return $newProduct;
    }
}
