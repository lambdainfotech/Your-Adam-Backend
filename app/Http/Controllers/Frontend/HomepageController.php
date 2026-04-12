<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\HomepageService;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    private HomepageService $homepageService;

    public function __construct(HomepageService $homepageService)
    {
        $this->homepageService = $homepageService;
    }

    /**
     * Get homepage data
     */
    public function index(): JsonResponse
    {
        $data = $this->homepageService->getHomepageData();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
