<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PricingService;
use App\Services\ProductApiTransformer;
use App\Services\StockManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected PricingService $pricingService;
    protected StockManagerService $stockManager;
    protected ProductApiTransformer $transformer;

    public function __construct(PricingService $pricingService, StockManagerService $stockManager, ProductApiTransformer $transformer)
    {
        $this->pricingService = $pricingService;
        $this->stockManager = $stockManager;
        $this->transformer = $transformer;
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
            return $this->transformer->transform($product);
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
            'data' => $this->transformer->transform($product, true),
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
            'data' => $this->transformer->transform($product, true),
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


}
