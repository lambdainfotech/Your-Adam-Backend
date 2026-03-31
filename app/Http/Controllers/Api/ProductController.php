<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PricingService;
use App\Services\StockManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected PricingService $pricingService;
    protected StockManagerService $stockManager;

    public function __construct(PricingService $pricingService, StockManagerService $stockManager)
    {
        $this->pricingService = $pricingService;
        $this->stockManager = $stockManager;
    }

    /**
     * List all products with variants
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'mainImage', 'variants.attributeValues.attribute', 'variants.mainImage']);

        // Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('product_type', $request->type);
        }

        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        if ($request->boolean('on_sale')) {
            $query->onSale();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->input('per_page', 20));

        // Transform data
        $products->getCollection()->transform(function ($product) {
            return $this->transformProduct($product);
        });

        return response()->json($products);
    }

    /**
     * Get single product with full details
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images', 'variants.attributeValues.attribute', 'variants.images', 'sizeChart']);

        return response()->json([
            'success' => true,
            'data' => $this->transformProduct($product, true),
        ]);
    }

    /**
     * Get product by slug
     */
    public function bySlug(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'images', 'variants.attributeValues.attribute', 'variants.images', 'sizeChart'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformProduct($product, true),
        ]);
    }

    /**
     * Check stock availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $results = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $variant = isset($item['variant_id']) ? \App\Models\Variant::find($item['variant_id']) : null;

            if ($variant) {
                $availability = $this->stockManager->checkAvailability($variant, $item['quantity']);
                $results[] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'available' => $availability['available'],
                    'message' => $availability['message'],
                    'available_quantity' => $availability['available_quantity'],
                ];
            } else {
                // For simple products
                $available = $product->product_type === 'simple' && 
                           $product->is_in_stock && 
                           (!$product->manage_stock || $product->stock_quantity >= $item['quantity']);
                
                $results[] = [
                    'product_id' => $item['product_id'],
                    'variant_id' => null,
                    'available' => $available,
                    'message' => $available ? 'In stock' : ($product->stock_quantity <= 0 ? 'Out of stock' : 'Insufficient stock'),
                    'available_quantity' => $product->manage_stock ? $product->stock_quantity : 999,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get product price (with sale calculation)
     */
    public function getPrice(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'variant_id' => 'nullable|exists:variants,id',
        ]);

        $priceData = [
            'product_id' => $product->id,
            'product_type' => $product->product_type,
            'base_price' => $product->base_price,
            'final_price' => $product->final_price,
            'is_on_sale' => $product->is_on_sale,
            'sale_schedule' => $this->pricingService->getSaleSchedule($product),
        ];

        if ($request->filled('variant_id')) {
            $variant = $product->variants()->find($request->variant_id);
            if ($variant) {
                $priceData['variant_id'] = $variant->id;
                $priceData['variant_price'] = $variant->price;
                $priceData['variant_final_price'] = $variant->final_price;
            }
        }

        // If variable product without specific variant, return price range
        if ($product->product_type === 'variable' && !$request->filled('variant_id')) {
            $priceData['price_range'] = $this->pricingService->getVariableProductPriceRange($product);
        }

        return response()->json([
            'success' => true,
            'data' => $priceData,
        ]);
    }

    /**
     * Get variant by attribute values
     */
    public function findVariant(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'attribute_values' => 'required|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $variant = $product->hasVariantWithAttributes($request->attribute_values);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found for given attribute combination',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'final_price' => $variant->final_price,
                'stock_quantity' => $variant->stock_quantity,
                'stock_status' => $variant->stock_status,
                'is_in_stock' => $variant->is_in_stock,
                'attribute_text' => $variant->attribute_text,
                'image' => $variant->mainImage?->full_image_url,
            ],
        ]);
    }

    /**
     * Transform product for API response
     */
    private function transformProduct(Product $product, bool $full = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'product_type' => $product->product_type,
            'short_description' => $product->short_description,
            'base_price' => $product->base_price,
            'final_price' => $product->final_price,
            'compare_price' => $product->compare_price,
            'is_on_sale' => $product->is_on_sale,
            'sale_schedule' => $this->pricingService->getSaleSchedule($product),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'main_image' => $product->mainImage ? $product->mainImage->full_image_url : null,
            'is_in_stock' => $product->is_in_stock,
            'total_stock' => $product->total_stock,
            'is_featured' => $product->is_featured,
        ];

        if ($product->product_type === 'variable') {
            $data['price_range'] = $this->pricingService->getVariableProductPriceRange($product);
        }

        if ($full) {
            $data['description'] = $product->description;
            $data['images'] = $product->images->map(fn($img) => [
                'id' => $img->id,
                'url' => $img->full_image_url,
                'is_main' => $img->is_main,
            ]);

            $data['variants'] = $product->variants->map(fn($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'final_price' => $variant->final_price,
                'compare_price' => $variant->compare_price,
                'stock_quantity' => $variant->stock_quantity,
                'stock_status' => $variant->stock_status,
                'is_in_stock' => $variant->is_in_stock,
                'is_active' => $variant->is_active,
                'weight' => $variant->weight,
                'attribute_text' => $variant->attribute_text,
                'attribute_values' => $variant->attributeValues->map(fn($av) => [
                    'id' => $av->id,
                    'attribute_name' => $av->attribute->name,
                    'value' => $av->value,
                    'color_code' => $av->color_code,
                ]),
                'image' => $variant->mainImage?->full_image_url,
            ]);

            $data['attributes'] = $product->productAttributes->map(fn($pa) => [
                'id' => $pa->attribute->id,
                'name' => $pa->attribute->name,
                'values' => $pa->attribute->values->map(fn($v) => [
                    'id' => $v->id,
                    'value' => $v->value,
                    'color_code' => $v->color_code,
                ]),
            ]);
        }

        return $data;
    }
}
