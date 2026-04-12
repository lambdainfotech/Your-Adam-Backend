<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;

class SiteInfoService
{
    /**
     * Get all site information in structured format
     */
    public function getSiteInfo(): array
    {
        $settings = Setting::allSettings();

        return [
            'site' => $this->getSiteSettings($settings),
            'contact' => $this->getContactSettings($settings),
            'social' => $this->getSocialSettings($settings),
            'announcement' => $this->getAnnouncementSettings($settings),
            'features' => $this->getFeatureSettings($settings),
            'header' => $this->getHeaderSettings(),
            'footer' => $this->getFooterSettings($settings),
        ];
    }

    /**
     * Get site branding and SEO settings
     */
    private function getSiteSettings(array $settings): array
    {
        return [
            'name' => $settings['site_name'] ?? 'Your Adam',
            'tagline' => $settings['site_tagline'] ?? 'Premium Fashion & Custom Apparel',
            'logo' => [
                'url' => $settings['site_logo_url'] ?? 'https://cdn.youradam.com/logo.png',
                'darkUrl' => $settings['site_logo_dark_url'] ?? 'https://cdn.youradam.com/logo-dark.png',
                'favicon' => $settings['site_favicon'] ?? 'https://cdn.youradam.com/favicon.ico',
                'appleTouchIcon' => $settings['site_apple_touch_icon'] ?? 'https://cdn.youradam.com/apple-touch-icon.png',
            ],
            'colors' => [
                'primary' => $settings['site_color_primary'] ?? '#f59e0b',
                'primaryDark' => $settings['site_color_primary_dark'] ?? '#d97706',
                'secondary' => $settings['site_color_secondary'] ?? '#0f172a',
                'accent' => $settings['site_color_accent'] ?? '#10b981',
                'background' => $settings['site_color_background'] ?? '#ffffff',
                'surface' => $settings['site_color_surface'] ?? '#f8fafc',
            ],
            'fonts' => [
                'heading' => $settings['site_font_heading'] ?? 'Playfair Display',
                'body' => $settings['site_font_body'] ?? 'Inter',
            ],
            'seo' => [
                'title' => $settings['site_seo_title'] ?? 'Your Adam | Premium Fashion & Custom Apparel',
                'description' => $settings['site_seo_description'] ?? 'Discover premium quality fashion and create custom designs with Your Adam. Bangladesh\'s leading print-on-demand and custom merchandise platform.',
                'keywords' => $settings['site_seo_keywords'] ?? 'ecommerce, fashion, custom t-shirts, print on demand, bangladesh, apparel',
                'ogImage' => $settings['site_seo_og_image'] ?? 'https://cdn.youradam.com/og-image.jpg',
            ],
        ];
    }

    /**
     * Get contact information settings
     */
    private function getContactSettings(array $settings): array
    {
        return [
            'email' => $settings['contact_email'] ?? 'support@youradam.com',
            'phone' => $settings['contact_phone'] ?? '+880 1234-567890',
            'whatsapp' => $settings['contact_whatsapp'] ?? '+880 1234-567890',
            'address' => [
                'street' => $settings['contact_address_street'] ?? 'House 12, Road 5',
                'area' => $settings['contact_address_area'] ?? 'Dhanmondi',
                'city' => $settings['contact_address_city'] ?? 'Dhaka',
                'postcode' => $settings['contact_address_postcode'] ?? '1205',
                'country' => $settings['contact_address_country'] ?? 'Bangladesh',
            ],
            'businessHours' => [
                'weekdays' => $settings['contact_hours_weekdays'] ?? '9:00 AM - 8:00 PM',
                'weekend' => $settings['contact_hours_weekend'] ?? '10:00 AM - 6:00 PM',
            ],
        ];
    }

    /**
     * Get social media links - fully dynamic
     * Admin can add/remove any social platform by creating settings with key pattern: social_{platform}
     * Example: social_tiktok, social_discord, social_github, etc.
     */
    private function getSocialSettings(array $settings): array
    {
        $socialLinks = [];

        // Find all settings that start with 'social_' (except internal ones like social_enabled if needed)
        foreach ($settings as $key => $value) {
            if (str_starts_with($key, 'social_') && !empty($value)) {
                // Extract platform name: social_facebook -> facebook
                $platform = substr($key, 7); // Remove 'social_' prefix
                $socialLinks[$platform] = $value;
            }
        }

        // Return default links if no social settings found
        if (empty($socialLinks)) {
            return [
                'facebook' => 'https://facebook.com/youradam',
                'instagram' => 'https://instagram.com/youradam',
                'twitter' => 'https://twitter.com/youradam',
                'youtube' => 'https://youtube.com/youradam',
                'linkedin' => 'https://linkedin.com/company/youradam',
            ];
        }

        return $socialLinks;
    }

    /**
     * Get announcement banner settings
     */
    private function getAnnouncementSettings(array $settings): array
    {
        return [
            'enabled' => filter_var($settings['announcement_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'message' => $settings['announcement_message'] ?? 'Free shipping on orders over ৳2,000',
            'link' => $settings['announcement_link'] ?? '/design',
            'linkText' => $settings['announcement_link_text'] ?? 'Create Custom Design',
            'backgroundColor' => $settings['announcement_bg_color'] ?? '#0f172a',
            'textColor' => $settings['announcement_text_color'] ?? '#ffffff',
        ];
    }

    /**
     * Get feature flags and settings
     */
    private function getFeatureSettings(array $settings): array
    {
        return [
            'freeShippingThreshold' => (int) ($settings['feature_free_shipping_threshold'] ?? 2000),
            'currency' => $settings['feature_currency'] ?? 'BDT',
            'currencySymbol' => $settings['feature_currency_symbol'] ?? '৳',
            'codAvailable' => filter_var($settings['feature_cod_available'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'returnsDays' => (int) ($settings['feature_returns_days'] ?? 7),
        ];
    }

    /**
     * Get header settings with categories for navigation
     */
    private function getHeaderSettings(): array
    {
        return [
            'categories' => $this->getHeaderCategories(),
        ];
    }

    /**
     * Get categories formatted for header navigation
     */
    private function getHeaderCategories(): array
    {
        // Get all active parent categories with their children
        $parentCategories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = [];

        foreach ($parentCategories as $parent) {
            $categoryData = [
                'id' => 'cat_' . $this->generateCategoryId($parent->slug),
                'name' => $parent->name,
                'href' => '/' . $parent->slug,
                'image' => $parent->image ?? null,
                'subcategories' => [],
            ];

            // Add subcategories if any
            foreach ($parent->children as $child) {
                if ($child->is_active) {
                    $categoryData['subcategories'][] = [
                        'id' => 'sub_' . $this->generateCategoryId($child->slug),
                        'name' => $child->name,
                        'href' => '/' . $parent->slug . '/' . $child->slug,
                        'image' => $child->image ?? null,
                    ];
                }
            }

            $categories[] = $categoryData;
        }

        return $categories;
    }

    /**
     * Generate a clean category ID from slug
     */
    private function generateCategoryId(string $slug): string
    {
        // Convert slug to lowercase, replace hyphens with underscores
        return str_replace('-', '_', strtolower($slug));
    }

    /**
     * Get footer settings with navigation links and brand info
     */
    private function getFooterSettings(array $settings): array
    {
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
}
