<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\SiteInfoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    private SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
    }

    /**
     * Get FAQ page data with categories and questions
     */
    public function index(Request $request): JsonResponse
    {
        $siteInfo = $this->siteInfoService->getSiteInfo();

        $query = Faq::with('category')
            ->where('is_active', true)
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        $faqs = $query->ordered()->get();

        // Group FAQs by category
        $grouped = [];
        foreach ($faqs as $faq) {
            $categorySlug = $faq->category->slug;
            if (!isset($grouped[$categorySlug])) {
                $grouped[$categorySlug] = [
                    'id' => $faq->category->id,
                    'name' => $faq->category->name,
                    'slug' => $faq->category->slug,
                    'icon' => $faq->category->icon,
                    'description' => $faq->category->description,
                    'faqs' => [],
                ];
            }
            $grouped[$categorySlug]['faqs'][] = [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
            ];
        }

        return $this->success([
            'page' => $siteInfo['faqPage'] ?? [],
            'categories' => array_values($grouped),
        ], 'FAQ data retrieved successfully');
    }

    /**
     * Get all active FAQ categories
     */
    public function categories(): JsonResponse
    {
        $categories = FaqCategory::active()
            ->ordered()
            ->withCount(['faqs' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'description' => $category->description,
                    'faqCount' => $category->faqs_count,
                ];
            });

        return $this->success($categories, 'FAQ categories retrieved successfully');
    }
}
