<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;

class SocialShareService
{
    /**
     * Available social share platforms with their URL templates
     */
    private array $platforms = [
        'facebook' => [
            'name' => 'Facebook',
            'icon' => 'facebook',
            'share_url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
            'color' => '#1877F2',
            'action' => 'share',
        ],
        'twitter' => [
            'name' => 'Twitter',
            'icon' => 'twitter',
            'share_url' => 'https://twitter.com/intent/tweet?url={url}&text={text}',
            'color' => '#1DA1F2',
            'action' => 'share',
        ],
        'instagram' => [
            'name' => 'Instagram',
            'icon' => 'instagram',
            'share_url' => '{url}',
            'color' => '#E4405F',
            'action' => 'copy_link',
        ],
        'linkedin' => [
            'name' => 'LinkedIn',
            'icon' => 'linkedin',
            'share_url' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
            'color' => '#0A66C2',
            'action' => 'share',
        ],
        'pinterest' => [
            'name' => 'Pinterest',
            'icon' => 'pinterest',
            'share_url' => 'https://pinterest.com/pin/create/button/?url={url}&media={image}&description={text}',
            'color' => '#E60023',
            'action' => 'share',
        ],
        'tiktok' => [
            'name' => 'TikTok',
            'icon' => 'tiktok',
            'share_url' => '{url}',
            'color' => '#000000',
            'action' => 'copy_link',
        ],
        'whatsapp' => [
            'name' => 'WhatsApp',
            'icon' => 'whatsapp',
            'share_url' => 'https://api.whatsapp.com/send?text={text}%20{url}',
            'color' => '#25D366',
            'action' => 'share',
        ],
        'telegram' => [
            'name' => 'Telegram',
            'icon' => 'telegram',
            'share_url' => 'https://t.me/share/url?url={url}&text={text}',
            'color' => '#0088cc',
            'action' => 'share',
        ],
        'reddit' => [
            'name' => 'Reddit',
            'icon' => 'reddit',
            'share_url' => 'https://www.reddit.com/submit?url={url}&title={text}',
            'color' => '#FF4500',
            'action' => 'share',
        ],
        'email' => [
            'name' => 'Email',
            'icon' => 'envelope',
            'share_url' => 'mailto:?subject={text}&body={url}',
            'color' => '#EA4335',
            'action' => 'share',
        ],
    ];

    /**
     * Get enabled social share platforms from settings
     */
    public function getEnabledPlatforms(): array
    {
        $enabled = json_decode(Setting::get('social_share_enabled_platforms', '[]'), true);

        if (empty($enabled)) {
            $enabled = ['facebook', 'twitter', 'whatsapp', 'linkedin', 'pinterest'];
        }

        $result = [];
        foreach ($enabled as $platform) {
            if (isset($this->platforms[$platform])) {
                $result[$platform] = $this->platforms[$platform];
            }
        }

        return $result;
    }

    /**
     * Generate share links for a given product
     */
    public function getProductShareLinks(Product $product): array
    {
        $platforms = $this->getEnabledPlatforms();
        $productUrl = $this->getProductUrl($product);
        $productText = urlencode($product->name);
        $productImage = urlencode($product->mainImage?->full_image_url ?? '');

        $links = [];
        foreach ($platforms as $key => $config) {
            $url = str_replace(
                ['{url}', '{text}', '{image}'],
                [urlencode($productUrl), $productText, $productImage],
                $config['share_url']
            );

            $links[] = [
                'platform' => $key,
                'name' => $config['name'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'action' => $config['action'],
                'share_url' => $url,
            ];
        }

        return $links;
    }

    /**
     * Get share configuration (platforms list without product-specific URLs)
     */
    public function getShareConfig(): array
    {
        $platforms = $this->getEnabledPlatforms();
        $result = [];

        foreach ($platforms as $key => $config) {
            $result[] = [
                'platform' => $key,
                'name' => $config['name'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'action' => $config['action'],
                'share_url_template' => $config['share_url'],
            ];
        }

        return [
            'enabled' => !empty($result),
            'platforms' => $result,
        ];
    }

    /**
     * Generate product URL for sharing
     */
    private function getProductUrl(Product $product): string
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        $slug = $product->slug ?? $product->id;

        return rtrim($baseUrl, '/') . '/products/' . $slug;
    }
}
