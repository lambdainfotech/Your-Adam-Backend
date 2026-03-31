<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'E-Commerce Store', 'group' => 'general', 'type' => 'string'],
            ['key' => 'app_logo', 'value' => null, 'group' => 'general', 'type' => 'string'],
            ['key' => 'app_favicon', 'value' => null, 'group' => 'general', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Asia/Dhaka', 'group' => 'general', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'd M, Y', 'group' => 'general', 'type' => 'string'],
            ['key' => 'time_format', 'value' => 'h:i A', 'group' => 'general', 'type' => 'string'],
            
            // Store
            ['key' => 'store_name', 'value' => 'My E-Commerce Store', 'group' => 'store', 'type' => 'string'],
            ['key' => 'store_address', 'value' => '123 Main Street, City, Country', 'group' => 'store', 'type' => 'text'],
            ['key' => 'store_phone', 'value' => '+8801234567890', 'group' => 'store', 'type' => 'string'],
            ['key' => 'store_email', 'value' => 'store@example.com', 'group' => 'store', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'BDT', 'group' => 'store', 'type' => 'string'],
            ['key' => 'currency_symbol', 'value' => '৳', 'group' => 'store', 'type' => 'string'],
            
            // Contact
            ['key' => 'contact_phone', 'value' => '+8801234567890', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => 'support@example.com', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'contact_address', 'value' => '123 Main Street, Dhaka, Bangladesh', 'group' => 'contact', 'type' => 'text'],
            
            // Social
            ['key' => 'facebook_url', 'value' => null, 'group' => 'social', 'type' => 'string'],
            ['key' => 'twitter_url', 'value' => null, 'group' => 'social', 'type' => 'string'],
            ['key' => 'instagram_url', 'value' => null, 'group' => 'social', 'type' => 'string'],
            ['key' => 'youtube_url', 'value' => null, 'group' => 'social', 'type' => 'string'],
            
            // Email
            ['key' => 'mail_from_address', 'value' => 'noreply@example.com', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => 'E-Commerce Store', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_driver', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port', 'value' => '587', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_username', 'value' => null, 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_password', 'value' => null, 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            
            // SMS
            ['key' => 'sms_provider', 'value' => null, 'group' => 'sms', 'type' => 'string'],
            ['key' => 'sms_api_key', 'value' => null, 'group' => 'sms', 'type' => 'string'],
            ['key' => 'sms_api_secret', 'value' => null, 'group' => 'sms', 'type' => 'string'],
            ['key' => 'sms_sender_id', 'value' => null, 'group' => 'sms', 'type' => 'string'],
            
            // Payment
            ['key' => 'payment_method_cod', 'value' => '1', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'payment_method_sslcommerz', 'value' => '0', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'payment_method_stripe', 'value' => '0', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'sslcommerz_store_id', 'value' => null, 'group' => 'payment', 'type' => 'string'],
            ['key' => 'sslcommerz_store_password', 'value' => null, 'group' => 'payment', 'type' => 'string'],
            ['key' => 'sslcommerz_sandbox', 'value' => '1', 'group' => 'payment', 'type' => 'boolean'],
            
            // Shipping
            ['key' => 'free_shipping_threshold', 'value' => '1000', 'group' => 'shipping', 'type' => 'number'],
            ['key' => 'default_shipping_cost', 'value' => '60', 'group' => 'shipping', 'type' => 'number'],
            ['key' => 'enable_courier_tracking', 'value' => '1', 'group' => 'shipping', 'type' => 'boolean'],
            
            // SEO
            ['key' => 'meta_title', 'value' => 'E-Commerce Store - Best Online Shopping', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'meta_description', 'value' => 'Shop online for the best products at great prices.', 'group' => 'seo', 'type' => 'text'],
            ['key' => 'meta_keywords', 'value' => 'ecommerce, shopping, online store', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'google_analytics_id', 'value' => null, 'group' => 'seo', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
