<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductSearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    use ApiResponse;
    private ProductSearchService $searchService;

    public function __construct(ProductSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search products by query
     */
    public function search(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'q' => 'nullable|string|min:1|max:255',
            'category_slug' => 'nullable|string|max:255',
            'subcategory_slug' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $results = $this->searchService->search($request);

        return $this->success($results, 'Search results retrieved successfully');
    }
}
