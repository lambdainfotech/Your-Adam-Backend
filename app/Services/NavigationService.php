<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;

class NavigationService
{
    /**
     * Get navigation data (header and footer)
     */
    public function getNavigation(): array
    {
        return [
            'header' => $this->getHeaderNavigation(),
            'footer' => $this->getFooterNavigation(),
        ];
    }

    /**
     * Get header navigation with categories
     */
    private function getHeaderNavigation(): array
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

        $categories = [];

        foreach ($parentCategories as $parent) {
            $categoryData = [
                'id' => 'cat_' . $this->generateId($parent->slug),
                'name' => $parent->name,
                'href' => '/' . $parent->slug,
                'subcategories' => [],
            ];

            // Add subcategories
            foreach ($parent->children as $child) {
                $categoryData['subcategories'][] = [
                    'id' => 'sub_' . $this->generateId($child->slug),
                    'name' => $child->name,
                    'href' => '/' . $parent->slug . '/' . $child->slug,
                ];
            }

            $categories[] = $categoryData;
        }

        return [
            'categories' => $categories,
        ];
    }

    /**
     * Get footer navigation
     */
    private function getFooterNavigation(): array
    {
        $settings = Setting::allSettings();

        // Parse JSON settings
        $supportLinks = json_decode($settings['footer_support_links'] ?? '[]', true);
        $companyLinks = json_decode($settings['footer_company_links'] ?? '[]', true);
        $trustBadges = json_decode($settings['footer_trust_badges'] ?? '[]', true);
        $paymentMethods = json_decode($settings['footer_payment_methods'] ?? '["Visa","Mastercard","bKash","Nagad"]', true);

        // Fallback defaults if empty
        if (empty($supportLinks)) {
            $supportLinks = [
                ['name' => 'Contact Us', 'href' => '/contact'],
                ['name' => 'FAQs', 'href' => '/faqs'],
                ['name' => 'Shipping Info', 'href' => '/shipping'],
                ['name' => 'Returns', 'href' => '/returns'],
            ];
        }

        if (empty($companyLinks)) {
            $companyLinks = [
                ['name' => 'About Us', 'href' => '/about'],
                ['name' => 'Careers', 'href' => '/careers'],
                ['name' => 'Terms', 'href' => '/terms'],
                ['name' => 'Privacy', 'href' => '/privacy'],
            ];
        }

        if (empty($trustBadges)) {
            $trustBadges = [
                ['icon' => 'Truck', 'text' => 'Free shipping over ৳2,000'],
                ['icon' => 'Shield', 'text' => 'Secure payment'],
                ['icon' => 'RotateCcw', 'text' => '7-day easy returns'],
                ['icon' => 'CreditCard', 'text' => 'COD available'],
            ];
        }

        if (empty($paymentMethods)) {
            $paymentMethods = ['Visa', 'Mastercard', 'bKash', 'Nagad'];
        }

        // Generate copyright if empty
        $copyright = $settings['footer_copyright'] ?? '';
        if (empty($copyright)) {
            $copyright = '© ' . date('Y') . ' ' . ($settings['site_name'] ?? 'Your Adam') . '. All rights reserved.';
        }

        return [
            'shop' => $this->getFooterShopLinks(),
            'support' => $supportLinks,
            'company' => $companyLinks,
            'trustBadges' => $trustBadges,
            'paymentMethods' => $paymentMethods,
            'brand' => [
                'description' => $settings['footer_brand_description'] ?? 'Premium fashion meets custom expression. Design your own or choose from our curated collections.',
                'copyright' => $copyright,
            ],
        ];
    }

    /**
     * Get shop links from active parent categories
     */
    private function getFooterShopLinks(): array
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $links = [];

        foreach ($categories as $category) {
            $links[] = [
                'name' => $category->name,
                'href' => '/' . $category->slug,
            ];
        }

        // Add custom design and corporate if not already in categories
        $hasDesign = $categories->contains('slug', 'design');
        $hasCorporate = $categories->contains('slug', 'corporate');

        if (!$hasDesign) {
            $links[] = ['name' => 'Custom Design', 'href' => '/design'];
        }
        if (!$hasCorporate) {
            $links[] = ['name' => 'Corporate', 'href' => '/corporate'];
        }

        return $links;
    }

    /**
     * Generate clean ID from slug
     */
    private function generateId(string $slug): string
    {
        return str_replace('-', '_', strtolower($slug));
    }
}
