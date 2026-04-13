<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchService
{
    /**
     * Search products by query
     */
    public function search(Request $request): array
    {
        $query = $request->get('q');
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 20);

        // Build search query
        $searchQuery = Product::with(['category', 'images', 'variants' => function ($q) {
            $q->where('is_active', true)
                ->orderBy('position');
        }])
            ->where('status', 1);

        // Apply search on multiple fields
        if ($query) {
            $searchTerm = '%' . $query . '%';
            $searchQuery->where(function ($q) use ($searchTerm, $query) {
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

        // Get total count before pagination
        $total = $searchQuery->count();

        // Apply pagination
        $products = $searchQuery->latest()
            ->skip(($page - 1) * $limit)
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
            'query' => $query,
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
