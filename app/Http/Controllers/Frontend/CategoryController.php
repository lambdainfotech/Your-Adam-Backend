<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CategoryApiService;
use App\Services\CategoryDetailService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;
    private CategoryApiService $categoryService;
    private CategoryDetailService $categoryDetailService;

    public function __construct(CategoryApiService $categoryService, CategoryDetailService $categoryDetailService)
    {
        $this->categoryService = $categoryService;
        $this->categoryDetailService = $categoryDetailService;
    }

    /**
     * Get all categories with subcategories and filters
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getCategories();

        return $this->success($categories, 'Categories retrieved successfully');
    }

    /**
     * Get single category detail with products and filters
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $data = $this->categoryDetailService->getCategoryDetail($slug, $request);

        if (!$data) {
            return $this->error('Category not found', 404);
        }

        return $this->success($data, 'Category retrieved successfully');
    }
}
