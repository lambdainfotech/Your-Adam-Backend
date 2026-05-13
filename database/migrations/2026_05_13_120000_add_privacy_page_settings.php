<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'privacy_page_title' => [
                'value' => 'Privacy Policy',
                'group' => 'privacy',
            ],
            'privacy_page_subtitle' => [
                'value' => 'We value your privacy and are committed to protecting your personal data.',
                'group' => 'privacy',
            ],
            'privacy_page_description' => [
                'value' => 'This Privacy Policy describes how Your Adam collects, uses, and protects your personal information when you use our website and services.',
                'group' => 'privacy',
            ],
            'privacy_page_hero_image' => [
                'value' => '',
                'group' => 'privacy',
            ],
            'privacy_page_last_updated' => [
                'value' => now()->format('F d, Y'),
                'group' => 'privacy',
            ],
            'privacy_page_sections' => [
                'value' => json_encode([
                    [
                        'title' => 'Introduction',
                        'content' => 'Your Adam ("we", "us", or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website youradam.com or use our services.

Please read this Privacy Policy carefully. If you do not agree with the terms of this Privacy Policy, please do not access the site or use our services.',
                    ],
                    [
                        'title' => 'Information We Collect',
                        'content' => 'We collect information that you provide directly to us, including:

• Personal identifiers such as your name, email address, phone number, and shipping address.
• Account information including username, password, and purchase history.
• Payment information processed securely through our payment partners.
• Communications you send to us, including customer support inquiries and feedback.

We also automatically collect certain information when you visit our website, including:

• Device and browser information, IP address, and operating system.
• Usage data such as pages visited, time spent on pages, and referral sources.
• Cookies and similar tracking technologies to enhance your browsing experience.',
                    ],
                    [
                        'title' => 'How We Use Your Information',
                        'content' => 'We use the information we collect for various purposes, including:

• To process and fulfill your orders, including shipping and delivery.
• To communicate with you about your orders, account, and promotions.
• To improve our website, products, and services based on user feedback and behavior.
• To personalize your shopping experience and recommend products you may like.
• To detect and prevent fraud, unauthorized transactions, and other illegal activities.
• To comply with legal obligations and enforce our terms and policies.',
                    ],
                    [
                        'title' => 'Data Sharing & Disclosure',
                        'content' => 'We do not sell or rent your personal information to third parties. We may share your information with:

• Service providers who assist us in operating our business, such as payment processors, shipping partners, and email service providers.
• Legal authorities when required by law or to protect our rights, property, or safety.
• Business partners with your consent or as part of a joint promotion.
• Affiliated companies within our corporate group for operational purposes.

All third-party service providers are contractually obligated to keep your information confidential and secure.',
                    ],
                    [
                        'title' => 'Cookies & Tracking Technologies',
                        'content' => 'We use cookies and similar technologies to:

• Remember your preferences and settings for a better browsing experience.
• Analyze website traffic and user behavior to improve our services.
• Deliver targeted advertising and measure the effectiveness of our campaigns.

You can manage your cookie preferences through your browser settings. Please note that disabling cookies may affect the functionality of our website.',
                    ],
                    [
                        'title' => 'Data Security',
                        'content' => 'We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. These measures include:

• SSL encryption for all data transmitted between your browser and our servers.
• Regular security audits and vulnerability assessments.
• Access controls and authentication procedures for our systems.
• Secure data storage with regular backups.

While we strive to protect your personal information, no method of transmission over the Internet or electronic storage is 100% secure.',
                    ],
                    [
                        'title' => 'Your Rights',
                        'content' => 'Depending on your location, you may have the following rights regarding your personal data:

• Right to access — request a copy of the personal data we hold about you.
• Right to rectification — request correction of inaccurate or incomplete data.
• Right to erasure — request deletion of your personal data under certain conditions.
• Right to restrict processing — request limitation on how we use your data.
• Right to data portability — request transfer of your data to another service.
• Right to object — object to certain types of processing, such as direct marketing.

To exercise any of these rights, please contact us using the information provided below.',
                    ],
                    [
                        'title' => 'Changes to This Policy',
                        'content' => 'We may update this Privacy Policy from time to time to reflect changes in our practices, legal requirements, or operational needs. When we make significant changes, we will notify you by:

• Posting the updated policy on our website with a revised effective date.
• Sending an email notification to the address associated with your account.
• Displaying a prominent notice on our website.

We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.',
                    ],
                    [
                        'title' => 'Contact Us',
                        'content' => 'If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:

Email: privacy@youradam.com
Phone: +880 1234-567890
Address: House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh

We are committed to addressing your concerns and will respond to all inquiries within 7 business days.',
                    ],
                ]),
                'group' => 'privacy',
            ],
            'privacy_page_show_last_updated' => [
                'value' => '1',
                'group' => 'privacy',
            ],
            'privacy_page_show_contact_cta' => [
                'value' => '1',
                'group' => 'privacy',
            ],
            'privacy_page_contact_cta_text' => [
                'value' => 'Have questions about our privacy practices? Contact our team.',
                'group' => 'privacy',
            ],
            'privacy_page_contact_cta_button' => [
                'value' => 'Contact Us',
                'group' => 'privacy',
            ],
            'privacy_page_contact_cta_link' => [
                'value' => '/contact',
                'group' => 'privacy',
            ],
            'privacy_page_meta_title' => [
                'value' => 'Privacy Policy | Your Adam',
                'group' => 'privacy',
            ],
            'privacy_page_meta_description' => [
                'value' => 'Learn how Your Adam collects, uses, and protects your personal information.',
                'group' => 'privacy',
            ],
        ];

        foreach ($settings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'group' => $data['group'],
                ]
            );
        }
    }

    public function down(): void
    {
        $keys = [
            'privacy_page_title',
            'privacy_page_subtitle',
            'privacy_page_description',
            'privacy_page_hero_image',
            'privacy_page_last_updated',
            'privacy_page_sections',
            'privacy_page_show_last_updated',
            'privacy_page_show_contact_cta',
            'privacy_page_contact_cta_text',
            'privacy_page_contact_cta_button',
            'privacy_page_contact_cta_link',
            'privacy_page_meta_title',
            'privacy_page_meta_description',
        ];

        Setting::whereIn('key', $keys)->delete();
    }
};
