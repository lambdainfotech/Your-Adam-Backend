<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryDetailService
{
    /**
     * Get category detail with products and filters
     */
    public function getCategoryDetail(string $slug, Request $request): ?array
    {
        // Find category by slug (could be parent or child)
        $category = Category::with(['children' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name');
        }, 'parent'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return null;
        }

        // Build breadcrumb
        $breadcrumb = $this->buildBreadcrumb($category);

        // Get products with filters
        $productsData = $this->getProducts($category, $request);

        // Get filters
        $filters = $this->getFilters($category, $request);

        return [
            'category' => [
                'id' => 'cat_' . $this->generateId($category->slug),
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'heroImage' => $category->hero_image,
                'coverImage' => $category->cover_image,
                'breadcrumb' => $breadcrumb,
            ],
            'filters' => $filters,
            'products' => $productsData,
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Newest'],
                ['value' => 'price-asc', 'label' => 'Price: Low to High'],
                ['value' => 'price-desc', 'label' => 'Price: High to Low'],
                ['value' => 'popular', 'label' => 'Most Popular'],
                ['value' => 'rating', 'label' => 'Highest Rated'],
            ],
        ];
    }

    /**
     * Build breadcrumb for category
     */
    private function buildBreadcrumb(Category $category): array
    {
        $breadcrumb = [
            ['name' => 'Home', 'href' => '/'],
        ];

        // If it's a child category, add parent first
        if ($category->parent) {
            $breadcrumb[] = [
                'name' => $category->parent->name,
                'href' => '/' . $category->parent->slug,
            ];
        }

        $breadcrumb[] = [
            'name' => $category->name,
            'href' => '/' . $category->slug,
        ];

        return $breadcrumb;
    }

    /**
     * Get products for category with filtering and pagination
     */
    private function getProducts(Category $category, Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 20);
        $sort = $request->get('sort', 'newest');
        $minPrice = $request->get('minPrice');
        $maxPrice = $request->get('maxPrice');
        $sizes = $request->get('sizes') ? explode(',', $request->get('sizes')) : [];
        $colors = $request->get('colors') ? explode(',', $request->get('colors')) : [];
        $inStock = $request->boolean('inStock');

        // Determine how to filter products based on category type
        $isParentCategory = $category->children && $category->children->count() > 0;

        // Build query
        $query = Product::with(['category', 'subCategory', 'images', 'variants' => function ($q) {
            $q->where('is_active', true)
                ->orderBy('position');
        }]);

        if ($isParentCategory) {
            // Parent category: match category_id (main category)
            $query->where('category_id', $category->id);
        } else {
            // Sub-category: match sub_category_id, fallback to category_id for backward compatibility
            $query->where(function ($q) use ($category) {
                $q->where('sub_category_id', $category->id)
                  ->orWhere('category_id', $category->id);
            });
        }

        $query->where('status', 1);

        // Apply price filter
        if ($minPrice !== null) {
            $query->where('base_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('base_price', '<=', $maxPrice);
        }

        // Apply in-stock filter
        if ($inStock) {
            $query->where('total_stock', '>', 0);
        }

        // Apply size filter
        if (!empty($sizes)) {
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->whereHas('attributeValues', function ($qv) use ($sizes) {
                    $qv->where('attribute_values.attribute_id', 1) // Size attribute
                        ->whereIn('attribute_values.value', $sizes);
                });
            });
        }

        // Apply color filter
        if (!empty($colors)) {
            $query->whereHas('variants', function ($q) use ($colors) {
                $q->whereHas('attributeValues', function ($qv) use ($colors) {
                    $qv->where('attribute_values.attribute_id', 2) // Color attribute
                        ->whereIn('attribute_values.value', $colors);
                });
            });
        }

        // Apply sorting
        switch ($sort) {
            case 'price-asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            case 'rating':
                $query->orderBy('created_at', 'desc'); // Fallback since no rating column
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        $products = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Format products
        $formattedProducts = $products->map(function ($product) {
            return $this->formatProduct($product);
        });

        return [
            'data' => $formattedProducts,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
        ];
    }

    /**
     * Get filters for category
     */
    private function getFilters(Category $category, Request $request): array
    {
        // Determine how to filter products based on category type
        $isParentCategory = $category->children && $category->children->count() > 0;

        $productQuery = Product::where('status', 1);

        if ($isParentCategory) {
            // Parent category: match category_id (main category)
            $productQuery->where('category_id', $category->id);
        } else {
            // Sub-category: match sub_category_id, fallback to category_id for backward compatibility
            $productQuery->where(function ($q) use ($category) {
                $q->where('sub_category_id', $category->id)
                  ->orWhere('category_id', $category->id);
            });
        }

        $productIds = $productQuery->pluck('id');

        $variantIds = Variant::whereIn('product_id', $productIds)
            ->pluck('id');

        // Get sizes
        $sizes = DB::table('variant_attribute_values')
            ->join('attribute_values', 'variant_attribute_values.attribute_value_id', '=', 'attribute_values.id')
            ->whereIn('variant_attribute_values.variant_id', $variantIds)
            ->where('attribute_values.attribute_id', 1)
            ->select('attribute_values.value', 'attribute_values.sort_order')
            ->distinct()
            ->orderBy('attribute_values.sort_order')
            ->orderBy('attribute_values.value')
            ->get()
            ->pluck('value')
            ->toArray();

        // Get colors
        $colors = DB::table('variant_attribute_values')
            ->join('attribute_values', 'variant_attribute_values.attribute_value_id', '=', 'attribute_values.id')
            ->whereIn('variant_attribute_values.variant_id', $variantIds)
            ->where('attribute_values.attribute_id', 2)
            ->select('attribute_values.value as name', 'attribute_values.color_code as code')
            ->distinct()
            ->get()
            ->map(function ($color) {
                return [
                    'name' => $this->formatColorName($color->name),
                    'code' => $color->code ?: $this->getColorCode($color->name),
                    'count' => 0,
                ];
            })
            ->toArray();

        // Get price range
        $priceRange = $this->getPriceRange($productIds);

        // Get subcategories with count
        $subcategories = [];
        if ($category->children && $category->children->count() > 0) {
            foreach ($category->children as $child) {
                // Count products by sub_category_id, fallback to category_id
                $childProductCount = Product::where(function ($q) use ($child) {
                        $q->where('sub_category_id', $child->id)
                          ->orWhere('category_id', $child->id);
                    })
                    ->where('status', 1)
                    ->count();
                
                $subcategories[] = [
                    'id' => 'cat_' . $this->generateId($category->slug) . '_' . $this->generateId($child->slug),
                    'name' => $child->name,
                    'count' => $childProductCount,
                ];
            }
        }

        return [
            'sizes' => $sizes,
            'colors' => $colors,
            'priceRange' => $priceRange ?: ['min' => 0, 'max' => 0],
            'subcategories' => $subcategories,
        ];
    }

    /**
     * Format product for API
     */
    private function formatProduct(Product $product): array
    {
        $basePrice = (float) $product->base_price;
        $salePrice = $product->compare_price ? (float) $product->compare_price : null;

        // Get stock status
        $stockType = 'IN_STOCK';
        if ($product->total_stock <= 0) {
            $stockType = 'OUT_OF_STOCK';
        } elseif ($product->total_stock < 5) {
            $stockType = 'LOW_STOCK';
        }

        return [
            'id' => 'prod_' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->short_description ?: 'Premium comfort',
            'basePrice' => $basePrice,
            'salePrice' => $salePrice,
            'sku' => $product->sku_prefix . '-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
            'stockType' => $stockType,
            'category' => $product->category ? [
                'id' => 'cat_' . $this->generateId($product->category->slug),
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'subCategory' => $product->subCategory ? [
                'id' => 'cat_' . $this->generateId($product->subCategory->slug),
                'name' => $product->subCategory->name,
                'slug' => $product->subCategory->slug,
            ] : null,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => 'img_' . $image->id,
                    'url' => $image->image_url,
                    'isDefault' => $image->is_main,
                ];
            })->toArray(),
            'variants' => $this->formatVariants($product->variants),
            'tags' => [],
            'isFeatured' => $product->is_featured,
            'rating' => 4.8,
            'reviewCount' => 124,
        ];
    }

    /**
     * Format variants
     */
    private function formatVariants($variants): array
    {
        return $variants->map(function ($variant) {
            $size = null;
            $color = null;
            $colorCode = null;

            foreach ($variant->attributeValues as $attrValue) {
                if ($attrValue->attribute_id == 1) {
                    $size = $attrValue->value;
                }
                if ($attrValue->attribute_id == 2) {
                    $color = $attrValue->value;
                    $colorCode = $attrValue->color_code ?: $this->getColorCode($attrValue->value);
                }
            }

            return [
                'id' => 'var_' . $variant->id,
                'size' => $size,
                'color' => $color,
                'colorCode' => $colorCode,
                'sku' => $variant->sku,
                'stockQty' => $variant->stock_quantity,
                'priceAdj' => (float) ($variant->price - $variant->product->base_price),
            ];
        })->toArray();
    }

    /**
     * Get price range
     */
    private function getPriceRange($productIds): ?array
    {
        $variants = Variant::whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        if ($variants && ($variants->min_price || $variants->max_price)) {
            return [
                'min' => (int) $variants->min_price,
                'max' => (int) $variants->max_price,
            ];
        }

        $products = Product::whereIn('id', $productIds)
            ->selectRaw('MIN(base_price) as min_price, MAX(base_price) as max_price')
            ->first();

        if ($products && ($products->min_price || $products->max_price)) {
            return [
                'min' => (int) $products->min_price,
                'max' => (int) $products->max_price,
            ];
        }

        return null;
    }

    /**
     * Format color name
     */
    private function formatColorName(string $color): string
    {
        $colorMap = [
            'black' => 'Black',
            'white' => 'White',
            'navy' => 'Navy Blue',
            'navy_blue' => 'Navy Blue',
            'gray' => 'Gray',
            'grey' => 'Gray',
            'olive' => 'Olive',
            'red' => 'Red',
            'blue' => 'Blue',
            'green' => 'Green',
            'yellow' => 'Yellow',
            'orange' => 'Orange',
            'pink' => 'Pink',
            'purple' => 'Purple',
            'brown' => 'Brown',
            'beige' => 'Beige',
            'cream' => 'Cream',
            'maroon' => 'Maroon',
            'teal' => 'Teal',
            'cyan' => 'Cyan',
            'magenta' => 'Magenta',
            'lime' => 'Lime',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
            'turquoise' => 'Turquoise',
            'gold' => 'Gold',
            'silver' => 'Silver',
            'bronze' => 'Bronze',
        ];

        $colorLower = strtolower($color);
        return $colorMap[$colorLower] ?? ucwords(str_replace(['_', '-'], ' ', $color));
    }

    /**
     * Get color hex code
     */
    private function getColorCode(string $color): string
    {
        $colorCodes = [
            'black' => '#000000',
            'white' => '#ffffff',
            'navy' => '#1e3a5f',
            'navy_blue' => '#1e3a5f',
            'gray' => '#6b7280',
            'grey' => '#6b7280',
            'olive' => '#556b2f',
            'red' => '#ef4444',
            'blue' => '#3b82f6',
            'green' => '#10b981',
            'yellow' => '#f59e0b',
            'orange' => '#f97316',
            'pink' => '#ec4899',
            'purple' => '#8b5cf6',
            'brown' => '#92400e',
            'beige' => '#d4c4b0',
            'cream' => '#fffdd0',
            'maroon' => '#800000',
            'teal' => '#14b8a6',
            'cyan' => '#06b6d4',
            'magenta' => '#d946ef',
            'lime' => '#84cc16',
            'indigo' => '#6366f1',
            'violet' => '#8b5cf6',
            'turquoise' => '#40e0d0',
            'gold' => '#ffd700',
            'silver' => '#c0c0c0',
            'bronze' => '#cd7f32',
        ];

        $colorLower = strtolower($color);
        return $colorCodes[$colorLower] ?? '#000000';
    }

    /**
     * Generate clean ID from slug
     */
    private function generateId(string $slug): string
    {
        return str_replace('-', '_', strtolower($slug));
    }
}
