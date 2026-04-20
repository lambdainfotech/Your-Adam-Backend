<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;

class RelatedProductService
{
    /**
     * Get related products for a product
     */
    public function getRelatedProducts(int $productId, int $limit = 8): array
    {
        $product = Product::with('category')->find($productId);
        
        if (!$product) {
            return [];
        }

        // Get products from same category
        $relatedQuery = Product::with(['category', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 1);

        // If not enough, get from parent category
        $related = $relatedQuery->take($limit)->get();

        if ($related->count() < $limit && $product->category && $product->category->parent_id) {
            $siblingCategoryIds = Category::where('parent_id', $product->category->parent_id)
                ->pluck('id');
            
            $additional = Product::with(['category', 'images'])
                ->whereIn('category_id', $siblingCategoryIds)
                ->where('id', '!=', $productId)
                ->whereNotIn('id', $related->pluck('id'))
                ->where('status', 1)
                ->take($limit - $related->count())
                ->get();
            
            $related = $related->merge($additional);
        }

        return $related->map(function ($item) {
            return $this->formatProduct($item);
        })->toArray();
    }

    /**
     * Get frequently bought together products
     */
    public function getFrequentlyBoughtTogether(int $productId, int $limit = 3): array
    {
        // This would typically analyze order data
        // For now, return products from same category with high ratings
        $product = Product::find($productId);
        
        if (!$product) {
            return [];
        }

        return Product::with(['category', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 1)
            ->where('is_featured', true)
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return $this->formatProduct($item);
            })
            ->toArray();
    }

    /**
     * Format product for API
     */
    private function formatProduct(Product $product): array
    {
        $basePrice = (float) $product->base_price;
        $finalPrice = (float) $product->final_price;
        $salePrice = $product->sale_price ? (float) $product->sale_price : null;
        $image = $product->images->first()?->image_url ?? $product->mainImage?->full_image_url;

        // Get real review summary
        $reviewSummary = \App\Models\Review::where('product_id', $product->id)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        return [
            'id' => 'prod_' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
            'legacy_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'base_price' => $basePrice,
            'final_price' => $finalPrice,
            'sale_price' => $salePrice,
            'image' => $image,
            'category' => $product->category ? [
                'id' => 'cat_' . str_replace('-', '_', strtolower($product->category->slug)),
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'rating' => $reviewSummary?->avg_rating ? round((float) $reviewSummary->avg_rating, 1) : 0,
            'review_count' => (int) ($reviewSummary?->total ?? 0),
        ];
    }
}
