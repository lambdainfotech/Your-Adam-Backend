<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchService
{
    protected ProductApiTransformer $transformer;

    public function __construct(ProductApiTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    /**
     * Search products by query
     */
    public function search(Request $request): array
    {
        $query = $request->get('q');
        $categorySlug = $request->get('category_slug');
        $subcategorySlug = $request->get('subcategory_slug');
        $name = $request->get('name');
        $slug = $request->get('slug');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 20);

        // Build search query
        $searchQuery = Product::with(['category', 'images', 'mainImage', 'variants.attributeValues.attribute', 'variants.mainImage'])
            ->where('is_active', true);

        // Prepare subcategories metadata if parent category is requested
        $subcategories = [];
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category && $category->parent_id === null) {
                $subcategories = $category->children()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($child) {
                        return [
                            'id' => 'cat_' . $this->generateId($child->slug),
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ];
                    })
                    ->toArray();
            }
        }

        // Apply general search on multiple fields
        if ($query) {
            $searchTerm = '%' . $query . '%';
            $searchQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                    ->orWhere('short_description', 'LIKE', $searchTerm)
                    ->orWhere('description', 'LIKE', $searchTerm)
                    ->orWhere('sku_prefix', 'LIKE', $searchTerm)
                    ->orWhere('slug', 'LIKE', $searchTerm)
                    ->orWhereHas('category', function ($cq) use ($searchTerm) {
                        $cq->where('name', 'LIKE', $searchTerm);
                    });
            });
        }

        // Apply category filter (includes subcategories when parent slug is provided)
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $categoryIds = [$category->id];
                if ($category->parent_id === null) {
                    // Parent category: include all subcategories
                    $descendantIds = $this->getDescendantCategoryIds($category->id);
                    $categoryIds = array_merge($categoryIds, $descendantIds);
                }
                $searchQuery->whereIn('category_id', $categoryIds);
            }
        }

        // Apply subcategory filter
        if ($subcategorySlug) {
            $searchQuery->whereHas('subCategory', function ($cq) use ($subcategorySlug) {
                $cq->where('slug', $subcategorySlug)
                    ->whereNotNull('parent_id');
            });
        }

        // Apply name filter
        if ($name) {
            $searchQuery->where('name', 'LIKE', '%' . $name . '%');
        }

        // Apply slug filter
        if ($slug) {
            $searchQuery->where('slug', 'LIKE', '%' . $slug . '%');
        }

        // Apply price range filters
        if ($minPrice !== null) {
            $searchQuery->where('base_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $searchQuery->where('base_price', '<=', $maxPrice);
        }

        // Get total count before pagination
        $total = $searchQuery->count();

        // Apply pagination
        $products = $searchQuery->latest()
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Format products
        $formattedProducts = $products->map(function ($product) {
            return $this->transformer->transform($product);
        });

        // Collect available variant attributes from results for filtering
        $variantAttributes = $this->collectVariantAttributes($products);

        return [
            'data' => $formattedProducts,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
            'filters' => [
                'q' => $query,
                'category_slug' => $categorySlug,
                'subcategory_slug' => $subcategorySlug,
                'name' => $name,
                'slug' => $slug,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
            'subcategories' => $subcategories,
            'variantAttributes' => $variantAttributes,
        ];
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
     * Collect unique variant attributes from products for filter sidebar
     */
    private function collectVariantAttributes($products): array
    {
        $sizes = [];
        $colors = [];

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                foreach ($variant->attributeValues as $attrValue) {
                    if ($attrValue->attribute_id == 1 && !in_array($attrValue->value, $sizes)) {
                        $sizes[] = $attrValue->value;
                    }
                    if ($attrValue->attribute_id == 2) {
                        $colorCode = $attrValue->color_code ?: $this->getColorCode($attrValue->value);
                        $colorKey = strtolower($attrValue->value);
                        if (!isset($colors[$colorKey])) {
                            $colors[$colorKey] = [
                                'name' => $attrValue->value,
                                'code' => $colorCode,
                            ];
                        }
                    }
                }
            }
        }

        $result = [];
        if (!empty($sizes)) {
            sort($sizes);
            $result[] = [
                'id' => 'attr_size',
                'name' => 'Size',
                'values' => array_values($sizes),
            ];
        }
        if (!empty($colors)) {
            $result[] = [
                'id' => 'attr_color',
                'name' => 'Color',
                'values' => array_values($colors),
            ];
        }

        return $result;
    }

    /**
     * Get all descendant category IDs recursively
     */
    private function getDescendantCategoryIds(int $categoryId): array
    {
        $ids = [];
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantCategoryIds($childId));
        }
        
        return $ids;
    }

    /**
     * Generate clean ID from slug
     */
    private function generateId(string $slug): string
    {
        return str_replace('-', '_', strtolower($slug));
    }
}
