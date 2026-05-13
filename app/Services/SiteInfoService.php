<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;
use App\Services\SocialShareService;

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
            'socialShare' => app(SocialShareService::class)->getShareConfig(),
            'announcement' => $this->getAnnouncementSettings($settings),
            'features' => $this->getFeatureSettings($settings),
            'payment' => $this->getPaymentSettings($settings),
            'shipping' => $this->getShippingSettings($settings),
            'header' => $this->getHeaderSettings(),
            'footer' => $this->getFooterSettings($settings),
            'contactPage' => $this->getContactPageSettings($settings),
            'faqPage' => $this->getFaqPageSettings($settings),
            'returnsPage' => $this->getReturnsPageSettings($settings),
            'aboutPage' => $this->getAboutPageSettings($settings),
            'termsPage' => $this->getTermsPageSettings($settings),
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
                'url' => $this->resolveAssetUrl($settings['site_logo_url'] ?? null, 'https://cdn.youradam.com/logo.png'),
                'darkUrl' => $this->resolveAssetUrl($settings['site_logo_dark_url'] ?? null, 'https://cdn.youradam.com/logo-dark.png'),
                'favicon' => $this->resolveAssetUrl($settings['site_favicon'] ?? null, 'https://cdn.youradam.com/favicon.ico'),
                'appleTouchIcon' => $this->resolveAssetUrl($settings['site_apple_touch_icon'] ?? null, 'https://cdn.youradam.com/apple-touch-icon.png'),
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
     * Get payment settings from admin payment configuration
     */
    private function getPaymentSettings(array $settings): array
    {
        $methods = [];

        if (filter_var($settings['payment_method_cod'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
            $methods[] = [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive',
            ];
        }

        if (filter_var($settings['payment_method_aamarpay'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
            $methods[] = [
                'id' => 'aamarpay',
                'name' => 'aamarPay',
                'description' => 'Bangladesh payment gateway (bKash, Nagad, Cards)',
            ];
        }

        if (filter_var($settings['payment_method_sslcommerz'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $methods[] = [
                'id' => 'sslcommerz',
                'name' => 'SSLCommerz',
                'description' => 'Online payment gateway',
            ];
        }

        if (filter_var($settings['payment_method_stripe'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $methods[] = [
                'id' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Credit/Debit card payments',
            ];
        }

        return [
            'methods' => $methods,
            'paymentMethods' => array_column($methods, 'name'),
        ];
    }

    /**
     * Get shipping settings
     */
    private function getShippingSettings(array $settings): array
    {
        $isFreeShipping = filter_var($settings['free_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'freeShipping' => $isFreeShipping,
            'insideDhaka' => $isFreeShipping ? 0 : (float) ($settings['shipping_cost_inside_dhaka'] ?? $settings['default_shipping_cost'] ?? 60),
            'outsideDhaka' => $isFreeShipping ? 0 : (float) ($settings['shipping_cost_outside_dhaka'] ?? $settings['default_shipping_cost'] ?? 120),
            'freeShippingThreshold' => (float) ($settings['free_shipping_threshold'] ?? 1000),
            'enableCourierTracking' => filter_var($settings['enable_courier_tracking'] ?? true, FILTER_VALIDATE_BOOLEAN),
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
     * Resolve image URL to full absolute URL if it's a relative path
     */
    private function resolveAssetUrl(?string $value, string $default): string
    {
        if (empty($value)) {
            return $default;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset(ltrim($value, '/'));
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
     * Get contact page settings for frontend display
     */
    private function getContactPageSettings(array $settings): array
    {
        $locations = json_decode($settings['contact_page_locations'] ?? '[]', true);
        $faqs = json_decode($settings['contact_page_faqs'] ?? '[]', true);

        if (empty($locations)) {
            $locations = [
                [
                    'name' => 'Head Office',
                    'address' => 'House 12, Road 5, Dhanmondi, Dhaka 1205',
                    'phone' => $settings['contact_phone'] ?? '+880 1234-567890',
                    'email' => $settings['contact_email'] ?? 'support@youradam.com',
                    'hours' => 'Sat - Thu: 9:00 AM - 8:00 PM',
                ],
            ];
        }

        if (empty($faqs)) {
            $faqs = [
                [
                    'question' => 'What are your business hours?',
                    'answer' => 'We are open Saturday to Thursday from 9:00 AM to 8:00 PM. Our online store is open 24/7.',
                ],
                [
                    'question' => 'How can I track my order?',
                    'answer' => 'You can track your order using the tracking number sent to your email or by visiting the Track Order page.',
                ],
            ];
        }

        return [
            'title' => $settings['contact_page_title'] ?? 'Contact Us',
            'subtitle' => $settings['contact_page_subtitle'] ?? "We'd love to hear from you",
            'description' => $settings['contact_page_description'] ?? 'Have a question, feedback, or just want to say hello? Reach out to us and our team will get back to you as soon as possible.',
            'heroImage' => $this->resolveAssetUrl($settings['contact_page_hero_image'] ?? null, ''),
            'showMap' => filter_var($settings['contact_page_show_map'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'mapEmbedUrl' => $settings['contact_page_map_embed_url'] ?? '',
            'locations' => $locations,
            'faqs' => $faqs,
            'formEnabled' => filter_var($settings['contact_page_form_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'meta' => [
                'title' => $settings['contact_page_meta_title'] ?? 'Contact Us | Your Adam',
                'description' => $settings['contact_page_meta_description'] ?? 'Get in touch with Your Adam. We are here to help you with any questions or concerns.',
            ],
        ];
    }

    /**
     * Get FAQ page settings for frontend display
     */
    private function getFaqPageSettings(array $settings): array
    {
        return [
            'title' => $settings['faq_page_title'] ?? 'Frequently Asked Questions',
            'subtitle' => $settings['faq_page_subtitle'] ?? 'Find answers to common questions about our products, shipping, returns, and more.',
            'description' => $settings['faq_page_description'] ?? "Can't find what you're looking for? Feel free to contact our support team for personalized assistance.",
            'heroImage' => $this->resolveAssetUrl($settings['faq_page_hero_image'] ?? null, ''),
            'showSearch' => filter_var($settings['faq_page_show_search'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showContactCta' => filter_var($settings['faq_page_show_contact_cta'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'contactCta' => [
                'text' => $settings['faq_page_contact_cta_text'] ?? 'Still have questions? Contact our support team.',
                'button' => $settings['faq_page_contact_cta_button'] ?? 'Contact Us',
                'link' => $settings['faq_page_contact_cta_link'] ?? '/contact',
            ],
            'meta' => [
                'title' => $settings['faq_page_meta_title'] ?? 'FAQs | Your Adam',
                'description' => $settings['faq_page_meta_description'] ?? 'Find answers to frequently asked questions about Your Adam products, orders, shipping, returns, and more.',
            ],
        ];
    }

    /**
     * Get terms & conditions page settings for frontend display
     */
    private function getTermsPageSettings(array $settings): array
    {
        $sections = json_decode($settings['terms_page_sections'] ?? '[]', true);

        if (empty($sections)) {
            $sections = [
                [
                    'title' => 'Introduction',
                    'content' => 'Welcome to Your Adam. These Terms and Conditions govern your use of our website.',
                ],
                [
                    'title' => 'Use of Our Website',
                    'content' => 'You agree to use our Website only for lawful purposes.',
                ],
            ];
        }

        return [
            'title' => $settings['terms_page_title'] ?? 'Terms & Conditions',
            'subtitle' => $settings['terms_page_subtitle'] ?? 'Please read these terms carefully before using our website',
            'description' => $settings['terms_page_description'] ?? '',
            'heroImage' => $this->resolveAssetUrl($settings['terms_page_hero_image'] ?? null, ''),
            'lastUpdated' => $settings['terms_page_last_updated'] ?? now()->format('F d, Y'),
            'showLastUpdated' => filter_var($settings['terms_page_show_last_updated'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sections' => $sections,
            'showContactCta' => filter_var($settings['terms_page_show_contact_cta'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'contactCta' => [
                'text' => $settings['terms_page_contact_cta_text'] ?? 'Have questions about our terms? Contact our support team.',
                'button' => $settings['terms_page_contact_cta_button'] ?? 'Contact Us',
                'link' => $settings['terms_page_contact_cta_link'] ?? '/contact',
            ],
            'meta' => [
                'title' => $settings['terms_page_meta_title'] ?? 'Terms & Conditions | Your Adam',
                'description' => $settings['terms_page_meta_description'] ?? 'Read our Terms and Conditions to understand the rules and guidelines for using our website and services.',
            ],
        ];
    }

    /**
     * Get returns page settings for frontend display
     */
    private function getReturnsPageSettings(array $settings): array
    {
        $eligibility = json_decode($settings['returns_page_eligibility'] ?? '[]', true);
        $steps = json_decode($settings['returns_page_steps'] ?? '[]', true);
        $conditions = json_decode($settings['returns_page_conditions'] ?? '[]', true);

        if (empty($eligibility)) {
            $eligibility = [
                ['icon' => 'CheckCircle', 'title' => 'Unused & Unwashed', 'description' => 'Items must be in original condition with no signs of wear.'],
                ['icon' => 'Package', 'title' => 'Original Packaging', 'description' => 'All original packaging, tags, and labels must be intact.'],
                ['icon' => 'Clock', 'title' => 'Within 7 Days', 'description' => 'Return requests must be initiated within 7 days of delivery.'],
                ['icon' => 'Receipt', 'title' => 'Proof of Purchase', 'description' => 'A valid order number or receipt is required.'],
            ];
        }

        if (empty($steps)) {
            $steps = [
                ['step' => 1, 'title' => 'Request a Return', 'description' => 'Fill out the return form below or contact our support team with your order number.'],
                ['step' => 2, 'title' => 'Pack Your Items', 'description' => 'Place the items in original packaging with all tags attached. Include a copy of your invoice.'],
                ['step' => 3, 'title' => 'Ship to Us', 'description' => 'Send the package to our return address using a trackable shipping method.'],
                ['step' => 4, 'title' => 'Get Refunded', 'description' => 'Once we receive and inspect your return, we will process your refund within 5-7 business days.'],
            ];
        }

        if (empty($conditions)) {
            $conditions = [
                ['type' => 'allowed', 'text' => 'Items in original condition with tags attached.'],
                ['type' => 'allowed', 'text' => 'Defective or damaged items (with photo proof).'],
                ['type' => 'allowed', 'text' => 'Wrong item delivered.'],
                ['type' => 'not_allowed', 'text' => 'Items washed, worn, or altered.'],
                ['type' => 'not_allowed', 'text' => 'Customized or personalized products.'],
                ['type' => 'not_allowed', 'text' => 'Items without original packaging or tags.'],
                ['type' => 'not_allowed', 'text' => 'Items returned after 7 days of delivery.'],
            ];
        }

        return [
            'title' => $settings['returns_page_title'] ?? 'Returns & Exchanges',
            'subtitle' => $settings['returns_page_subtitle'] ?? 'Easy returns within 7 days of delivery',
            'description' => $settings['returns_page_description'] ?? 'We want you to love your purchase. If you are not completely satisfied, you can return your items within 7 days for a full refund or exchange.',
            'heroImage' => $this->resolveAssetUrl($settings['returns_page_hero_image'] ?? null, ''),
            'policySummary' => $settings['returns_page_policy_summary'] ?? '',
            'eligibility' => $eligibility,
            'steps' => $steps,
            'conditions' => $conditions,
            'refundInfo' => $settings['returns_page_refund_info'] ?? 'Refunds will be processed to your original payment method within 5-7 business days after we receive your returned items.',
            'returnAddress' => $settings['returns_page_return_address'] ?? "Your Adam Returns Department\nDhaka, Bangladesh",
            'formEnabled' => filter_var($settings['returns_page_form_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showContactCta' => filter_var($settings['returns_page_show_contact_cta'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'contactCta' => [
                'text' => $settings['returns_page_contact_cta_text'] ?? 'Need help with your return? Contact our support team.',
                'button' => $settings['returns_page_contact_cta_button'] ?? 'Contact Support',
                'link' => $settings['returns_page_contact_cta_link'] ?? '/contact',
            ],
            'meta' => [
                'title' => $settings['returns_page_meta_title'] ?? 'Returns & Exchanges | Your Adam',
                'description' => $settings['returns_page_meta_description'] ?? 'Learn about our easy return and exchange policy. Hassle-free returns within 7 days of delivery.',
            ],
        ];
    }

    /**
     * Get about page settings for frontend display
     */
    private function getAboutPageSettings(array $settings): array
    {
        $values = json_decode($settings['about_page_values'] ?? '[]', true);
        $stats = json_decode($settings['about_page_stats'] ?? '[]', true);
        $milestones = json_decode($settings['about_page_milestones'] ?? '[]', true);

        if (empty($values)) {
            $values = [
                ['icon' => 'Gem', 'title' => 'Quality First', 'description' => 'We never compromise on the quality of our products.'],
                ['icon' => 'Palette', 'title' => 'Creative Freedom', 'description' => 'Everyone should have tools to express their unique style.'],
                ['icon' => 'Heart', 'title' => 'Customer Obsessed', 'description' => 'Your satisfaction is our top priority.'],
                ['icon' => 'Leaf', 'title' => 'Sustainability', 'description' => 'Committed to reducing our environmental footprint.'],
            ];
        }

        if (empty($stats)) {
            $stats = [
                ['value' => '50K+', 'label' => 'Happy Customers'],
                ['value' => '100+', 'label' => 'Products'],
                ['value' => '4', 'label' => 'Years of Service'],
                ['value' => '99%', 'label' => 'Satisfaction Rate'],
            ];
        }

        if (empty($milestones)) {
            $milestones = [
                ['year' => '2020', 'title' => 'Founded', 'description' => 'Your Adam was founded in Dhaka.'],
                ['year' => '2021', 'title' => 'First 10K Customers', 'description' => 'Reached 10,000 happy customers.'],
                ['year' => '2022', 'title' => 'Expanded Product Line', 'description' => 'Launched corporate merchandise services.'],
                ['year' => '2023', 'title' => 'Nationwide Shipping', 'description' => 'Coverage to all 64 districts.'],
            ];
        }

        return [
            'title' => $settings['about_page_title'] ?? 'About Us',
            'subtitle' => $settings['about_page_subtitle'] ?? 'Premium fashion meets custom expression',
            'description' => $settings['about_page_description'] ?? '',
            'heroImage' => $this->resolveAssetUrl($settings['about_page_hero_image'] ?? null, ''),
            'story' => [
                'title' => $settings['about_page_story_title'] ?? 'Our Story',
                'content' => $settings['about_page_story_content'] ?? '',
            ],
            'mission' => [
                'title' => $settings['about_page_mission_title'] ?? 'Our Mission',
                'content' => $settings['about_page_mission_content'] ?? '',
            ],
            'vision' => [
                'title' => $settings['about_page_vision_title'] ?? 'Our Vision',
                'content' => $settings['about_page_vision_content'] ?? '',
            ],
            'values' => $values,
            'stats' => $stats,
            'showTeam' => filter_var($settings['about_page_show_team'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showMilestones' => filter_var($settings['about_page_show_milestones'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'milestones' => $milestones,
            'cta' => [
                'enabled' => filter_var($settings['about_page_cta_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'title' => $settings['about_page_cta_title'] ?? 'Want to work with us?',
                'text' => $settings['about_page_cta_text'] ?? '',
                'button' => $settings['about_page_cta_button'] ?? 'Get in Touch',
                'link' => $settings['about_page_cta_link'] ?? '/contact',
            ],
            'meta' => [
                'title' => $settings['about_page_meta_title'] ?? 'About Us | Your Adam',
                'description' => $settings['about_page_meta_description'] ?? 'Learn about Your Adam - Bangladesh\'s leading custom fashion brand.',
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
