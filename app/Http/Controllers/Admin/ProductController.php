<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\CodeGeneratorService;
use App\Services\ImageUploadService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected ImageUploadService $imageService;
    protected PricingService $pricingService;
    protected CodeGeneratorService $codeGenerator;

    public function __construct(
        ImageUploadService $imageService, 
        PricingService $pricingService,
        CodeGeneratorService $codeGenerator
    ) {
        $this->imageService = $imageService;
        $this->pricingService = $pricingService;
        $this->codeGenerator = $codeGenerator;
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants', 'mainImage']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('sku_prefix', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Product type filter
        if ($request->filled('type')) {
            $query->where('product_type', $request->type);
        }
        
        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')->where('stock_status', 'in_stock')
                          ->orWhere('product_type', 'variable')->whereHas('variants', function($qv) {
                              $qv->where('stock_status', 'in_stock');
                          });
                    });
                    break;
                case 'out_of_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')->where('stock_status', 'out_of_stock')
                          ->orWhere('product_type', 'variable')->whereDoesntHave('variants', function($qv) {
                              $qv->where('stock_status', 'in_stock');
                          });
                    });
                    break;
                case 'low_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->where('manage_stock', true)
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                    })->orWhere(function($q) {
                        $q->where('product_type', 'variable')
                          ->whereHas('variants', function($qv) {
                              $qv->where('manage_stock', true)
                                 ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                          });
                    });
                    break;
            }
        }
        
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $attributes = \App\Models\Attribute::with('values')->where('is_variation', true)->get();
        $predefinedDescriptions = \App\Models\PredefinedDescription::descriptions()->active()->orderBy('sort_order')->get();
        $predefinedShortDescriptions = \App\Models\PredefinedDescription::shortDescriptions()->active()->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories', 'attributes', 'predefinedDescriptions', 'predefinedShortDescriptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'predefined_description_id' => 'nullable|exists:predefined_descriptions,id',
            'predefined_short_description_id' => 'nullable|exists:predefined_descriptions,id',
            'product_type' => 'required|in:simple,variable',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'wholesale_percentage' => 'nullable|numeric|min:0|max:99.99',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            
            // Simple product fields
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'manage_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
            'low_stock_threshold' => 'nullable|integer|min:0',
            
            // Variable product fields
            'sku_prefix' => 'nullable|string|max:20',
            
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['manage_stock'] = $request->boolean('manage_stock', true);
        
        // Handle predefined descriptions
        if ($request->filled('predefined_description_id')) {
            $predefinedDesc = \App\Models\PredefinedDescription::find($request->predefined_description_id);
            $validated['description'] = $predefinedDesc->content;
        }
        
        if ($request->filled('predefined_short_description_id')) {
            $predefinedShortDesc = \App\Models\PredefinedDescription::find($request->predefined_short_description_id);
            $validated['short_description'] = $predefinedShortDesc->content;
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
        
        // Create product
        $product = Product::create($validated);
        
        // Auto-generate SKU and Barcode for simple products if not provided
        if ($product->product_type === 'simple') {
            $updateData = [];
            
            // Generate SKU if not provided
            if (empty($product->sku)) {
                $category = Category::find($product->category_id);
                $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : null;
                $updateData['sku'] = $this->codeGenerator->generateProductSku($categoryCode . '-PRD');
            }
            
            // Generate Barcode if not provided
            if (empty($product->barcode)) {
                $updateData['barcode'] = $this->codeGenerator->generateBarcode();
            }
            
            // Update product with generated codes
            if (!empty($updateData)) {
                $product->update($updateData);
            }
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
        
        // Redirect to variants page if variable product and variants not created
        if ($product->product_type === 'variable' && !$request->has('variants')) {
            return redirect()->route('admin.products.variants', $product)
                ->with('success', 'Product created. Now configure variants.');
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'variants.attributeValues.attribute', 'images', 'inventoryMovements' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        // Get price info
        $priceInfo = [
            'base_price' => $product->base_price,
            'final_price' => $product->final_price,
            'is_on_sale' => $product->is_on_sale,
            'sale_schedule' => $this->pricingService->getSaleSchedule($product),
        ];
        
        if ($product->product_type === 'variable') {
            $priceInfo['price_range'] = $this->pricingService->getVariableProductPriceRange($product);
        }
        
        return view('admin.products.show', compact('product', 'priceInfo'));
    }

    public function edit(Product $product)
    {
        $product->load(['variants.attributeValues', 'variationAttributes']);
        $categories = Category::where('is_active', true)->get();
        $attributes = \App\Models\Attribute::with('values')->where('is_variation', true)->get();
        $selectedAttributeIds = $product->variationAttributes->pluck('id')->toArray();
        $predefinedDescriptions = \App\Models\PredefinedDescription::descriptions()->active()->orderBy('sort_order')->get();
        $predefinedShortDescriptions = \App\Models\PredefinedDescription::shortDescriptions()->active()->orderBy('sort_order')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'selectedAttributeIds', 'predefinedDescriptions', 'predefinedShortDescriptions'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'predefined_description_id' => 'nullable|exists:predefined_descriptions,id',
            'predefined_short_description_id' => 'nullable|exists:predefined_descriptions,id',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'wholesale_percentage' => 'nullable|numeric|min:0|max:99.99',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,flat',
            'discount_value' => 'nullable|numeric|min:0',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
        
        // Add product-type specific rules
        if ($product->product_type === 'simple') {
            $rules['sku'] = 'nullable|string|max:50|unique:products,sku,' . $product->id;
            $rules['manage_stock'] = 'boolean';
            $rules['stock_quantity'] = 'nullable|integer|min:0';
            $rules['low_stock_threshold'] = 'nullable|integer|min:0';
        }
        
        $validated = $request->validate($rules);
        
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        
        // Handle predefined descriptions
        if ($request->filled('predefined_description_id')) {
            $predefinedDesc = \App\Models\PredefinedDescription::find($request->predefined_description_id);
            $validated['description'] = $predefinedDesc->content;
        }
        
        if ($request->filled('predefined_short_description_id')) {
            $predefinedShortDesc = \App\Models\PredefinedDescription::find($request->predefined_short_description_id);
            $validated['short_description'] = $predefinedShortDesc->content;
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
            $updateData = [];
            
            // Generate SKU if not provided
            if (empty($product->sku)) {
                $category = Category::find($product->category_id);
                $categoryCode = $category ? strtoupper(substr($category->slug, 0, 3)) : null;
                $updateData['sku'] = $this->codeGenerator->generateProductSku($categoryCode . '-PRD');
            }
            
            // Generate Barcode if not provided
            if (empty($product->barcode)) {
                $updateData['barcode'] = $this->codeGenerator->generateBarcode();
            }
            
            // Update product with generated codes
            if (!empty($updateData)) {
                $product->update($updateData);
            }
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
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Create variants for variable product
     */
    private function createVariants(Product $product, array $variantsData): void
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
            // Skip variants with stock quantity <= 0
            if (($variantData['stock_quantity'] ?? 0) <= 0) {
                continue;
            }

            // Get attribute values for SKU generation
            $attributeValues = [];
            if (!empty($variantData['attribute_values'])) {
                $valueIds = explode(',', $variantData['attribute_values']);
                $values = \App\Models\AttributeValue::whereIn('id', $valueIds)->get();
                $attributeValues = $values->pluck('value')->toArray();
            }
            
            // Generate SKU if not provided
            $sku = $variantData['sku'] ?? null;
            if (empty($sku)) {
                $sku = $this->codeGenerator->generateVariantSku(
                    $product, 
                    $attributeValues, 
                    $position
                );
            }
            
            // Generate Barcode if not provided (using related barcode strategy)
            $barcode = $variantData['barcode'] ?? null;
            if (empty($barcode)) {
                $barcode = $this->codeGenerator->generateVariantBarcode($product, $position);
            }
            
            // Create the variant
            $variant = $product->variants()->create([
                'sku' => $sku,
                'barcode' => $barcode,
                'price' => $variantData['price'] ?? $product->base_price,
                'wholesale_percentage' => $variantData['wholesale_percentage'] ?? null,
                'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                'stock_status' => ($variantData['stock_quantity'] ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
                'is_active' => $variantData['is_active'] ?? true,
                'position' => $position,
            ]);

            // Attach attribute values
            if (!empty($variantData['attribute_values'])) {
                $valueIds = explode(',', $variantData['attribute_values']);
                $variant->attributeValues()->attach($valueIds);
            }

            $position++;
        }

        // Update product has_variants flag
        $product->update(['has_variants' => true]);
    }

    public function destroy(Product $product)
    {
        // Check if product has orders
        if ($product->variants()->whereHas('orderItems')->exists()) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete product with existing orders.');
        }
        
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
    
    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();
        
        $status = $product->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.products.index')
            ->with('success', "Product {$status} successfully.");
    }

    /**
     * Quick update product stock (AJAX)
     */
    public function quickUpdateStock(Request $request, Product $product)
    {
        if ($product->product_type !== 'simple') {
            return response()->json(['error' => 'Can only update stock for simple products'], 400);
        }

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldStock = $product->stock_quantity;
        $newStock = $validated['stock_quantity'];

        $product->update([
            'stock_quantity' => $newStock,
            'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
        ]);

        // Log the change
        if ($newStock !== $oldStock) {
            \App\Models\InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => $newStock > $oldStock ? 'in' : 'out',
                'quantity' => abs($newStock - $oldStock),
                'reason' => $validated['reason'] ?? 'Quick stock update',
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'new_stock' => $newStock,
            'stock_status' => $product->stock_status,
        ]);
    }

    /**
     * Duplicate product
     */
    public function duplicate(Product $product)
    {
        $newProduct = $product->replicate();
        $newProduct->slug = $product->slug . '-copy-' . time();
        $newProduct->sku = $product->sku ? $product->sku . '-COPY' : null;
        $newProduct->sku_prefix = $product->sku_prefix ? $product->sku_prefix . '-COPY' : null;
        $newProduct->is_active = false;
        $newProduct->save();

        // Duplicate variants if variable product
        if ($product->product_type === 'variable') {
            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = $variant->sku . '-COPY';
                $newVariant->stock_quantity = 0;
                $newVariant->stock_status = 'out_of_stock';
                $newVariant->save();

                // Copy attribute values
                $newVariant->attributeValues()->attach($variant->attributeValues->pluck('id'));
            }
        }

        return redirect()->route('admin.products.edit', $newProduct)
            ->with('success', 'Product duplicated. Please review and activate.');
    }
}
