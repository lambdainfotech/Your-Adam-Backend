<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class CategoryApiService
{
    /**
     * Get all categories formatted for API
     */
    public function getCategories(): array
    {
        $parentCategories = Category::with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $parentCategories->map(function ($category) {
            return $this->formatCategory($category);
        })->toArray();
    }

    /**
     * Format a single category
     */
    private function formatCategory(Category $category): array
    {
        // Parent category: count products by category_id
        $productCount = Product::where('category_id', $category->id)
            ->where('status', 1)
            ->count();

        $data = [
            'id' => 'cat_' . $this->generateId($category->slug),
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => $category->image,
            'heroImage' => $category->hero_image,
            'coverImage' => $category->cover_image,
            'productCount' => $productCount,
            'sortOrder' => $category->sort_order,
            'isActive' => $category->is_active,
        ];

        // Add children if any
        if ($category->children && $category->children->count() > 0) {
            $data['children'] = $category->children->map(function ($child) use ($category) {
                return $this->formatChildCategory($child, $category);
            })->toArray();
        }

        return $data;
    }

    /**
     * Format child category
     */
    private function formatChildCategory(Category $child, Category $parent): array
    {
        // Child category: count products by sub_category_id (fallback to category_id)
        $productCount = Product::where(function ($query) use ($child) {
                $query->where('sub_category_id', $child->id)
                      ->orWhere('category_id', $child->id);
            })
            ->where('status', 1)
            ->count();

        $data = [
            'id' => 'cat_' . $this->generateId($parent->slug) . '_' . $this->generateId($child->slug),
            'name' => $child->name,
            'slug' => $child->slug,
            'parentId' => 'cat_' . $this->generateId($parent->slug),
            'productCount' => $productCount,
        ];

        // Add filters for categories with products
        if ($productCount > 0) {
            $filters = $this->getCategoryFilters($child->id);
            if (!empty($filters)) {
                $data['filters'] = $filters;
            }
        }

        return $data;
    }

    /**
     * Get filters for a category (sizes, colors, price range)
     */
    private function getCategoryFilters(int $categoryId): array
    {
        $filters = [];

        // Get product IDs in this category (check both category_id and sub_category_id)
        $productIds = Product::where(function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)
                      ->orWhere('sub_category_id', $categoryId);
            })
            ->where('status', 1)
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return $filters;
        }

        // Get variant IDs for these products
        $variantIds = Variant::whereIn('product_id', $productIds)
            ->pluck('id');

        // Get sizes from attribute_values (attribute_id = 1 for size)
        $sizes = DB::table('variant_attribute_values')
            ->join('attribute_values', 'variant_attribute_values.attribute_value_id', '=', 'attribute_values.id')
            ->whereIn('variant_attribute_values.variant_id', $variantIds)
            ->where('attribute_values.attribute_id', 1) // Size attribute
            ->distinct()
            ->pluck('attribute_values.value')
            ->sort()
            ->values()
            ->toArray();

        if (!empty($sizes)) {
            $filters['sizes'] = $sizes;
        }

        // Get colors from attribute_values (attribute_id = 2 for color)
        $colors = DB::table('variant_attribute_values')
            ->join('attribute_values', 'variant_attribute_values.attribute_value_id', '=', 'attribute_values.id')
            ->whereIn('variant_attribute_values.variant_id', $variantIds)
            ->where('attribute_values.attribute_id', 2) // Color attribute
            ->distinct()
            ->select('attribute_values.value as name', 'attribute_values.color_code as code')
            ->get()
            ->map(function ($color) {
                return [
                    'name' => $this->formatColorName($color->name),
                    'code' => $color->code ?: $this->getColorCode($color->name),
                ];
            })
            ->values()
            ->toArray();

        if (!empty($colors)) {
            $filters['colors'] = $colors;
        }

        // Get price range
        $priceRange = $this->getPriceRange($productIds);
        if ($priceRange) {
            $filters['priceRange'] = $priceRange;
        }

        return $filters;
    }

    /**
     * Get price range for products
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

        // Fallback to product base prices
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
