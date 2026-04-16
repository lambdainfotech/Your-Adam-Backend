<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;

class ProductApiTransformer
{
    protected PricingService $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Transform product to standardized API format
     */
    public function transform(Product $product, bool $full = false): array
    {
        $basePrice = (float) $product->base_price;
        $finalPrice = (float) $product->final_price;
        $salePrice = $product->sale_price ? (float) $product->sale_price : null;
        $comparePrice = $product->compare_price ? (float) $product->compare_price : null;
        $isOnSale = $product->is_on_sale;

        $stockType = $this->getStockType($product);
        $availabilityStatus = $product->is_in_stock ? 'in_stock' : 'out_of_stock';

        $priceRange = null;
        if ($product->product_type === 'variable') {
            $priceRange = $this->pricingService->getVariableProductPriceRange($product);
        }

        $saleSchedule = $this->pricingService->getSaleSchedule($product);

        $images = $this->formatImages($product);
        $variants = $this->formatVariants($product);
        $attributes = $this->formatAttributes($product);

        $data = [
            'id' => 'prod_' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT),
            'legacy_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description ?? '',
            'short_description' => $product->short_description ?? '',
            'product_type' => $product->product_type,
            'sku' => $product->sku_prefix ? $product->sku_prefix . '-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT) : null,
            'pricing' => [
                'currency' => 'BDT',
                'base_price' => $basePrice,
                'sale_price' => $salePrice,
                'final_price' => $finalPrice,
                'compare_price' => $comparePrice,
                'is_on_sale' => $isOnSale,
                'price_range' => $priceRange ?? [
                    'min' => $basePrice,
                    'max' => $basePrice,
                    'has_range' => false,
                ],
                'sale_schedule' => [
                    'is_on_sale' => $saleSchedule['is_on_sale'],
                    'sale_price' => $saleSchedule['sale_price'] ? (float) $saleSchedule['sale_price'] : null,
                    'regular_price' => (float) $saleSchedule['regular_price'],
                    'start_date' => $saleSchedule['start_date'],
                    'end_date' => $saleSchedule['end_date'],
                    'days_remaining' => $saleSchedule['days_remaining'],
                ],
            ],
            'inventory' => [
                'stock_type' => $stockType,
                'is_in_stock' => $product->is_in_stock,
                'total_stock' => $product->total_stock,
                'availability_status' => $availabilityStatus,
            ],
            'category' => $product->category ? [
                'id' => 'cat_' . $this->generateId($product->category->slug),
                'legacy_id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'media' => [
                'main_image' => $product->mainImage?->full_image_url ?? $images['images'][0]['url'] ?? null,
                'default_image' => $images['default_image'],
                'images' => $images['images'],
            ],
            'variants' => $variants,
            'attributes' => $attributes,
            'meta' => [
                'is_featured' => $product->is_featured,
                'tags' => [],
                'rating' => 4.8,
                'review_count' => 124,
            ],
            'timestamps' => [
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ],
        ];

        if ($full) {
            $data['seo'] = $this->formatSeo($product);
        }

        return $data;
    }

    private function getStockType(Product $product): string
    {
        if ($product->product_type === 'simple') {
            if ($product->stock_quantity <= 0) {
                return 'OUT_OF_STOCK';
            } elseif ($product->is_low_stock) {
                return 'LOW_STOCK';
            }
            return 'IN_STOCK';
        }

        // Variable product
        if (!$product->is_in_stock) {
            return 'OUT_OF_STOCK';
        }
        if ($product->is_low_stock) {
            return 'LOW_STOCK';
        }
        return 'IN_STOCK';
    }

    private function formatImages(Product $product): array
    {
        $imageList = [];
        $defaultImage = null;

        if ($product->relationLoaded('images')) {
            foreach ($product->images as $index => $image) {
                $formatted = [
                    'id' => 'img_' . ($index + 1),
                    'url' => $image->full_image_url,
                    'is_default' => (bool) $image->is_main,
                ];
                $imageList[] = $formatted;
                if ($image->is_main && !$defaultImage) {
                    $defaultImage = $formatted;
                }
            }
        }

        if (!$defaultImage && !empty($imageList)) {
            $defaultImage = $imageList[0];
        }

        if (!$defaultImage) {
            $defaultImage = [
                'id' => 'img_1',
                'url' => null,
            ];
        }

        return [
            'default_image' => $defaultImage,
            'images' => $imageList,
        ];
    }

    private function formatVariants(Product $product): array
    {
        $hasVariants = $product->product_type === 'variable' && $product->variants->isNotEmpty();

        if (!$hasVariants) {
            return [
                'has_variants' => false,
                'options' => [],
                'items' => [],
            ];
        }

        $options = [];
        $items = [];
        $colors = [];
        $sizes = [];

        foreach ($product->variants as $variant) {
            $size = null;
            $color = null;
            $colorCode = null;

            foreach ($variant->attributeValues as $attrValue) {
                $attrName = strtolower($attrValue->attribute->name ?? '');
                if (str_contains($attrName, 'size')) {
                    $size = $attrValue->value;
                    if (!in_array('size', $options)) {
                        $options[] = 'size';
                    }
                    if (!in_array($size, $sizes)) {
                        $sizes[] = $size;
                    }
                }
                if (str_contains($attrName, 'color')) {
                    $color = $attrValue->value;
                    $colorCode = $attrValue->color_code ?: $this->getColorCode($attrValue->value);
                    if (!in_array('color', $options)) {
                        $options[] = 'color';
                    }
                    if (!in_array($color, $colors)) {
                        $colors[] = $color;
                    }
                }
            }

            $items[] = [
                'id' => 'var_' . $variant->id,
                'legacy_id' => $variant->id,
                'color' => $color,
                'size' => $size,
                'color_code' => $colorCode ?? '#000000',
                'sku' => $variant->sku,
                'stock_qty' => $variant->stock_quantity,
                'price_adjustment' => (float) ($variant->price - $product->base_price),
                'is_in_stock' => $variant->is_in_stock,
                'is_active' => $variant->is_active,
                'image' => $variant->mainImage?->full_image_url ?? null,
            ];
        }

        return [
            'has_variants' => true,
            'options' => $options,
            'items' => $items,
        ];
    }

    private function formatAttributes(Product $product): array
    {
        $colors = [];
        $sizes = [];

        foreach ($product->variants as $variant) {
            foreach ($variant->attributeValues as $attrValue) {
                $attrName = strtolower($attrValue->attribute->name ?? '');
                if (str_contains($attrName, 'color') && !in_array($attrValue->value, $colors)) {
                    $colors[] = $attrValue->value;
                }
                if (str_contains($attrName, 'size') && !in_array($attrValue->value, $sizes)) {
                    $sizes[] = $attrValue->value;
                }
            }
        }

        // Also check product attributes if no variants found
        if (empty($colors) && empty($sizes) && $product->relationLoaded('productAttributes')) {
            foreach ($product->productAttributes as $pa) {
                $attrName = strtolower($pa->attribute->name ?? '');
                foreach ($pa->attribute->values as $value) {
                    if (str_contains($attrName, 'color') && !in_array($value->value, $colors)) {
                        $colors[] = $value->value;
                    }
                    if (str_contains($attrName, 'size') && !in_array($value->value, $sizes)) {
                        $sizes[] = $value->value;
                    }
                }
            }
        }

        return [
            'available_colors' => $colors,
            'available_sizes' => $sizes,
        ];
    }

    private function formatSeo(Product $product): array
    {
        $metaTitle = $product->seo_title ?: $product->name . ' - Buy Online in Bangladesh';
        $metaDescription = $product->seo_description ?: $product->short_description ?: 'Buy ' . $product->name . ' at best price in Bangladesh. Premium quality, fast delivery.';
        $canonicalUrl = url('/products/' . $product->slug);
        $mainImage = $product->mainImage?->full_image_url;

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => [
                $product->name,
                'buy online',
                'Bangladesh',
                $product->category?->name ?? '',
            ],
            'canonical_url' => $canonicalUrl,
            'slug' => $product->slug,
            'open_graph' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $mainImage,
                'url' => $canonicalUrl,
                'type' => 'product',
            ],
            'twitter_card' => [
                'card' => 'summary_large_image',
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $mainImage,
            ],
            'structured_data' => [
                '@context' => 'https://schema.org/',
                '@type' => 'Product',
                'name' => $product->name,
                'image' => $mainImage ? [$mainImage] : [],
                'description' => $product->short_description ?? '',
                'sku' => $product->sku_prefix ? $product->sku_prefix . '-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT) : null,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => config('app.name', 'Lambda Info Tech'),
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'BDT',
                    'price' => $product->final_price,
                    'availability' => $product->is_in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url' => $canonicalUrl,
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => 4.8,
                    'reviewCount' => 124,
                ],
            ],
        ];
    }

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

        return $colorCodes[strtolower($color)] ?? '#000000';
    }

    private function generateId(string $slug): string
    {
        return str_replace('-', '_', strtolower($slug));
    }
}
