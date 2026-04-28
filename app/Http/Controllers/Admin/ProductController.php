<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\PricingService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected CategoryService $categoryService;

    protected PricingService $pricingService;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        PricingService $pricingService,
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->pricingService = $pricingService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|integer|exists:categories,id',
            'status' => 'nullable|in:active,inactive',
            'type' => 'nullable|in:simple,variable',
            'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
        ]);

        $query = Product::with(['category', 'subCategory', 'variants', 'mainImage']);
        
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
            $selectedCat = Category::find($request->category);
            if ($selectedCat) {
                if ($selectedCat->parent_id === null) {
                    // Parent category selected — filter by category_id
                    $query->where('category_id', $request->category);
                } else {
                    // Sub-category or leaf category selected — filter by sub_category_id
                    $query->where('sub_category_id', $request->category);
                }
            }
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
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                          ->where('stock_quantity', '>', 0);
                    })->orWhere(function($q) {
                        $q->where('product_type', 'variable')
                          ->whereHas('variants', function($qv) {
                              $qv->where('manage_stock', true)
                                 ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                 ->where('stock_quantity', '>', 0);
                          });
                    });
                    break;
            }
        }
        
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $categories = $this->categoryService->getHierarchicalCategories();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = $this->categoryService->getHierarchicalCategories();
        $attributes = \App\Models\Attribute::with('values')->where('is_variation', true)->get();
        $predefinedDescriptions = \App\Models\PredefinedDescription::descriptions()->active()->orderBy('sort_order')->get();
        $predefinedShortDescriptions = \App\Models\PredefinedDescription::shortDescriptions()->active()->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories', 'attributes', 'predefinedDescriptions', 'predefinedShortDescriptions'));
    }

    public function store(Request $request)
    {
        try {
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
                'sku' => 'nullable|string|max:50|unique:products,sku',
                'barcode' => 'nullable|string|max:50|unique:products,barcode',
                'manage_stock' => 'boolean',
                'stock_quantity' => 'nullable|integer|min:0',
                'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'sku_prefix' => 'nullable|string|max:20',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            $product = $this->productService->createProduct($request, $validated);

            if ($product->product_type === 'variable' && !$request->has('variants')) {
                return redirect()->route('admin.products.variants', $product)
                    ->with('success', 'Product created. Now configure variants.');
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create product: ' . $e->getMessage())
                ->withInput();
        }
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
        $categories = $this->categoryService->getHierarchicalCategories();
        $attributes = \App\Models\Attribute::with('values')->where('is_variation', true)->get();
        $selectedAttributeIds = $product->variationAttributes->pluck('id')->toArray();
        $predefinedDescriptions = \App\Models\PredefinedDescription::descriptions()->active()->orderBy('sort_order')->get();
        $predefinedShortDescriptions = \App\Models\PredefinedDescription::shortDescriptions()->active()->orderBy('sort_order')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'selectedAttributeIds', 'predefinedDescriptions', 'predefinedShortDescriptions'));
    }

    public function update(Request $request, Product $product)
    {
        try {
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

            if ($product->product_type === 'simple') {
                $rules['sku'] = 'nullable|string|max:50|unique:products,sku,' . $product->id;
                $rules['barcode'] = 'nullable|string|max:50|unique:products,barcode,' . $product->id;
                $rules['manage_stock'] = 'boolean';
                $rules['stock_quantity'] = 'nullable|integer|min:0';
                $rules['low_stock_threshold'] = 'nullable|integer|min:0';
            }

            $validated = $request->validate($rules);

            $this->productService->updateProduct($request, $product, $validated);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update product: ' . $e->getMessage())
                ->withInput();
        }
    }



    public function destroy(Product $product)
    {
        try {
            // Check if product has orders (via variants or direct order items)
            if ($product->variants()->whereHas('orderItems')->exists() || $product->orderItems()->exists()) {
                return redirect()->route('admin.products.index')
                    ->with('error', 'Cannot delete product with existing orders.');
            }
            
            $product->delete();
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }
    
    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();
        
        $status = $product->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.products.index')
            ->with('success', "Product {$status} successfully.");
    }

    public function toggleBestSeller(Product $product)
    {
        $product->is_bestseller = !$product->is_bestseller;
        $product->save();
        
        $status = $product->is_bestseller ? 'marked as Best Seller' : 'removed from Best Seller';
        
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
        $newProduct = $this->productService->duplicateProduct($product);

        return redirect()->route('admin.products.edit', $newProduct)
            ->with('success', 'Product duplicated. Please review and activate.');
    }
}
