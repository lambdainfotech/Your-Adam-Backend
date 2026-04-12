<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Site Info
            [
                'key' => 'site_name',
                'value' => 'Your Adam',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Site name',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Premium Fashion & Custom Apparel',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Site tagline',
            ],

            // Logo Settings
            [
                'key' => 'site_logo_url',
                'value' => 'https://cdn.youradam.com/logo.png',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Logo URL',
            ],
            [
                'key' => 'site_logo_dark_url',
                'value' => 'https://cdn.youradam.com/logo-dark.png',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Dark mode logo URL',
            ],
            [
                'key' => 'site_favicon',
                'value' => 'https://cdn.youradam.com/favicon.ico',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Favicon URL',
            ],
            [
                'key' => 'site_apple_touch_icon',
                'value' => 'https://cdn.youradam.com/apple-touch-icon.png',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Apple touch icon URL',
            ],

            // Color Settings
            [
                'key' => 'site_color_primary',
                'value' => '#f59e0b',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Primary brand color',
            ],
            [
                'key' => 'site_color_primary_dark',
                'value' => '#d97706',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Primary dark color',
            ],
            [
                'key' => 'site_color_secondary',
                'value' => '#0f172a',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Secondary color',
            ],
            [
                'key' => 'site_color_accent',
                'value' => '#10b981',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Accent color',
            ],
            [
                'key' => 'site_color_background',
                'value' => '#ffffff',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Background color',
            ],
            [
                'key' => 'site_color_surface',
                'value' => '#f8fafc',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Surface color',
            ],

            // Font Settings
            [
                'key' => 'site_font_heading',
                'value' => 'Playfair Display',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Heading font family',
            ],
            [
                'key' => 'site_font_body',
                'value' => 'Inter',
                'group' => 'site',
                'type' => 'string',
                'description' => 'Body font family',
            ],

            // SEO Settings
            [
                'key' => 'site_seo_title',
                'value' => 'Your Adam | Premium Fashion & Custom Apparel',
                'group' => 'seo',
                'type' => 'string',
                'description' => 'Default SEO title',
            ],
            [
                'key' => 'site_seo_description',
                'value' => "Discover premium quality fashion and create custom designs with Your Adam. Bangladesh's leading print-on-demand and custom merchandise platform.",
                'group' => 'seo',
                'type' => 'text',
                'description' => 'Default SEO description',
            ],
            [
                'key' => 'site_seo_keywords',
                'value' => 'ecommerce, fashion, custom t-shirts, print on demand, bangladesh, apparel',
                'group' => 'seo',
                'type' => 'text',
                'description' => 'Default SEO keywords',
            ],
            [
                'key' => 'site_seo_og_image',
                'value' => 'https://cdn.youradam.com/og-image.jpg',
                'group' => 'seo',
                'type' => 'string',
                'description' => 'Default Open Graph image URL',
            ],

            // Contact Settings
            [
                'key' => 'contact_email',
                'value' => 'support@youradam.com',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Contact email address',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+880 1234-567890',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Contact phone number',
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '+880 1234-567890',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'WhatsApp number',
            ],

            // Address Settings
            [
                'key' => 'contact_address_street',
                'value' => 'House 12, Road 5',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Street address',
            ],
            [
                'key' => 'contact_address_area',
                'value' => 'Dhanmondi',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Area/District',
            ],
            [
                'key' => 'contact_address_city',
                'value' => 'Dhaka',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'City',
            ],
            [
                'key' => 'contact_address_postcode',
                'value' => '1205',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Postal code',
            ],
            [
                'key' => 'contact_address_country',
                'value' => 'Bangladesh',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Country',
            ],

            // Business Hours
            [
                'key' => 'contact_hours_weekdays',
                'value' => '9:00 AM - 8:00 PM',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Weekday business hours',
            ],
            [
                'key' => 'contact_hours_weekend',
                'value' => '10:00 AM - 6:00 PM',
                'group' => 'contact',
                'type' => 'string',
                'description' => 'Weekend business hours',
            ],

            // Social Media Links
            // Social Media Links - Dynamic (add any platform: social_tiktok, social_discord, etc.)
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/youradam',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Facebook page URL (key pattern: social_{platform})',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/youradam',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Instagram profile URL (key pattern: social_{platform})',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/youradam',
                'group' => 'social',
                'type' => 'string',
                'description' => 'Twitter/X profile URL (key pattern: social_{platform})',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/youradam',
                'group' => 'social',
                'type' => 'string',
                'description' => 'YouTube channel URL (key pattern: social_{platform})',
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/youradam',
                'group' => 'social',
                'type' => 'string',
                'description' => 'LinkedIn company URL (key pattern: social_{platform})',
            ],

            // Announcement Settings
            [
                'key' => 'announcement_enabled',
                'value' => 'true',
                'group' => 'announcement',
                'type' => 'boolean',
                'description' => 'Enable announcement banner',
            ],
            [
                'key' => 'announcement_message',
                'value' => 'Free shipping on orders over ৳2,000',
                'group' => 'announcement',
                'type' => 'string',
                'description' => 'Announcement message text',
            ],
            [
                'key' => 'announcement_link',
                'value' => '/design',
                'group' => 'announcement',
                'type' => 'string',
                'description' => 'Announcement link URL',
            ],
            [
                'key' => 'announcement_link_text',
                'value' => 'Create Custom Design',
                'group' => 'announcement',
                'type' => 'string',
                'description' => 'Announcement link text',
            ],
            [
                'key' => 'announcement_bg_color',
                'value' => '#0f172a',
                'group' => 'announcement',
                'type' => 'string',
                'description' => 'Announcement background color',
            ],
            [
                'key' => 'announcement_text_color',
                'value' => '#ffffff',
                'group' => 'announcement',
                'type' => 'string',
                'description' => 'Announcement text color',
            ],

            // Feature Settings
            [
                'key' => 'feature_free_shipping_threshold',
                'value' => '2000',
                'group' => 'features',
                'type' => 'number',
                'description' => 'Free shipping minimum order amount',
            ],
            [
                'key' => 'feature_currency',
                'value' => 'BDT',
                'group' => 'features',
                'type' => 'string',
                'description' => 'Default currency code',
            ],
            [
                'key' => 'feature_currency_symbol',
                'value' => '৳',
                'group' => 'features',
                'type' => 'string',
                'description' => 'Currency symbol',
            ],
            [
                'key' => 'feature_cod_available',
                'value' => 'true',
                'group' => 'features',
                'type' => 'boolean',
                'description' => 'Cash on delivery available',
            ],
            [
                'key' => 'feature_returns_days',
                'value' => '7',
                'group' => 'features',
                'type' => 'number',
                'description' => 'Return policy duration in days',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('Site settings seeded successfully!');
    }
}
