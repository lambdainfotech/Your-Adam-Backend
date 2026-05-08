<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SocialShareService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SocialShareController extends Controller
{
    use ApiResponse;

    private SocialShareService $socialShareService;

    public function __construct(SocialShareService $socialShareService)
    {
        $this->socialShareService = $socialShareService;
    }

    /**
     * Get social share configuration for frontend
     */
    public function config(): JsonResponse
    {
        $config = $this->socialShareService->getShareConfig();

        return $this->success($config, 'Social share config retrieved successfully');
    }

    /**
     * Get share links for a specific product
     */
    public function productShare(Product $product): JsonResponse
    {
        $links = $this->socialShareService->getProductShareLinks($product);

        return $this->success([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'share_links' => $links,
        ], 'Product share links retrieved successfully');
    }
}
