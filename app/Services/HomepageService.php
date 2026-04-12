<?php

namespace App\Services;

use App\Models\BrandValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Testimonial;

class HomepageService
{
    /**
     * Get all homepage data
     */
    public function getHomepageData(): array
    {
        return [
            'heroSlides' => $this->getHeroSlides(),
            'featuredCategories' => $this->getFeaturedCategories(),
            'newArrivals' => $this->getNewArrivals(),
            'bestSellers' => $this->getBestSellers(),
            'brandValues' => $this->getBrandValues(),
            'testimonials' => $this->getTestimonials(),
        ];
    }

    /**
     * Get hero slides from sliders
     */
    private function getHeroSlides(): array
    {
        $sliders = Slider::active()
            ->ordered()
            ->get();

        return $sliders->map(function ($slider) {
            $slide = [
                'id' => $slider->id,
                'title' => $slider->title,
                'subtitle' => $slider->subtitle,
                'description' => $slider->description,
                'image' => $slider->banner_image_url,
                'mobileImage' => $slider->mobile_image ? asset('storage/' . $slider->mobile_image) : null,
                'cta' => null,
                'secondaryCta' => null,
                'order' => $slider->sort_order,
                'active' => $slider->is_active,
            ];

            // Primary CTA
            if ($slider->button_text && $slider->button_url) {
                $slide['cta'] = [
                    'text' => $slider->button_text,
                    'href' => $slider->button_url,
                ];
            }

            // Secondary CTA
            if ($slider->secondary_button_text && $slider->secondary_button_url) {
                $slide['secondaryCta'] = [
                    'text' => $slider->secondary_button_text,
                    'href' => $slider->secondary_button_url,
                ];
            }

            return $slide;
        })->toArray();
    }

    /**
     * Get featured categories
     */
    private function getFeaturedCategories(): array
    {
        $categories = Category::withCount('products')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        return $categories->map(function ($category) {
            return [
                'id' => 'cat_' . $this->generateId($category->slug),
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $category->image,
                'productCount' => $category->products_count,
                'featured' => false,
            ];
        })->toArray();
    }

    /**
     * Get new arrivals products
     */
    private function getNewArrivals(): array
    {
        $products = Product::with(['category', 'images'])
            ->where('status', 1)
            ->latest()
            ->take(8)
            ->get();

        return [
            'title' => 'New Arrivals',
            'subtitle' => 'Latest additions to our collection',
            'viewAllHref' => '/men',
            'products' => $this->formatProducts($products),
        ];
    }

    /**
     * Get best sellers products
     */
    private function getBestSellers(): array
    {
        $products = Product::with(['category', 'images'])
            ->where('status', 1)
            ->where('is_featured', true)
            ->take(8)
            ->get();

        return [
            'title' => 'Best Sellers',
            'subtitle' => 'Most popular products right now',
            'viewAllHref' => '/men',
            'products' => $this->formatProducts($products),
        ];
    }

    /**
     * Get brand values
     */
    private function getBrandValues(): array
    {
        $values = BrandValue::active()
            ->ordered()
            ->get();

        // Return default values if none found
        if ($values->isEmpty()) {
            return [
                ['icon' => 'Palette', 'title' => 'Custom Design', 'description' => 'Create unique designs'],
                ['icon' => 'Shirt', 'title' => 'Premium Quality', 'description' => 'Best fabrics & craft'],
                ['icon' => 'Truck', 'title' => 'Fast Delivery', 'description' => '2-4 days nationwide'],
                ['icon' => 'Leaf', 'title' => 'Sustainable', 'description' => 'Eco-friendly materials'],
                ['icon' => 'Shield', 'title' => 'Secure Payment', 'description' => '100% secure checkout'],
                ['icon' => 'Clock', 'title' => 'Easy Returns', 'description' => '7-day return policy'],
            ];
        }

        return $values->map(function ($value) {
            return [
                'icon' => $value->icon,
                'title' => $value->title,
                'description' => $value->description,
            ];
        })->toArray();
    }

    /**
     * Get testimonials
     */
    private function getTestimonials(): array
    {
        $testimonials = Testimonial::active()
            ->ordered()
            ->take(6)
            ->get();

        // Return default testimonials if none found
        if ($testimonials->isEmpty()) {
            return [
                [
                    'id' => 1,
                    'name' => 'Rahman Ahmed',
                    'role' => 'Regular Customer',
                    'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop',
                    'content' => 'The quality of the t-shirts exceeded my expectations. The custom design tool was so easy to use, and my order arrived exactly as I designed it. Will definitely order again!',
                    'rating' => 5,
                ],
                [
                    'id' => 2,
                    'name' => 'Fatima Khan',
                    'role' => 'Business Owner',
                    'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop',
                    'content' => 'We\'ve been ordering corporate merchandise from Your Adam for over a year. Their attention to detail and customer service is outstanding. Our team loves the branded apparel!',
                    'rating' => 5,
                ],
                [
                    'id' => 3,
                    'name' => 'Kamal Hossain',
                    'role' => 'Designer',
                    'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop',
                    'content' => 'As a designer, I appreciate the quality of prints and fabrics. The design platform gives me the flexibility to create exactly what my clients want. Highly recommended!',
                    'rating' => 5,
                ],
            ];
        }

        return $testimonials->map(function ($testimonial) {
            return [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $testimonial->role,
                'avatar' => $testimonial->avatar,
                'content' => $testimonial->content,
                'rating' => (float) $testimonial->rating,
            ];
        })->toArray();
    }

    /**
     * Format products for API response
     */
    private function formatProducts($products): array
    {
        return $products->map(function ($product) {
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
                'variants' => [],
                'tags' => [],
                'isFeatured' => $product->is_featured,
                'rating' => 4.8,
                'reviewCount' => 124,
            ];
        })->toArray();
    }

    /**
     * Generate clean ID from slug
     */
    private function generateId(string $slug): string
    {
        return str_replace('-', '_', strtolower($slug));
    }
}
