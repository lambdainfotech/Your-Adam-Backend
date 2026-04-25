<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\HomepageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    use ApiResponse;
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

        return $this->success($data, 'Homepage data retrieved successfully');
    }
}
