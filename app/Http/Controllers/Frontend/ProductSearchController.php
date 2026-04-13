<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
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
            'q' => 'required|string|min:1|max:255',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $results = $this->searchService->search($request);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}
