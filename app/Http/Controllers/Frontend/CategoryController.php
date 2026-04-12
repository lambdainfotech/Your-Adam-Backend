<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryApiService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    private CategoryApiService $categoryService;

    public function __construct(CategoryApiService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories with subcategories and filters
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getCategories();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
