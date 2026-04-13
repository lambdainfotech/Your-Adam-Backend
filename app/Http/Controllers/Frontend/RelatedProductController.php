<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\RelatedProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelatedProductController extends Controller
{
    private RelatedProductService $relatedProductService;

    public function __construct(RelatedProductService $relatedProductService)
    {
        $this->relatedProductService = $relatedProductService;
    }

    /**
     * Get related products
     */
    public function index(int $productId, Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 8);
        $products = $this->relatedProductService->getRelatedProducts($productId, $limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get frequently bought together products
     */
    public function frequentlyBoughtTogether(int $productId, Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 3);
        $products = $this->relatedProductService->getFrequentlyBoughtTogether($productId, $limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
