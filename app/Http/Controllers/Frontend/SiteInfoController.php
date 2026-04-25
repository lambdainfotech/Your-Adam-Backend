<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteInfoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SiteInfoController extends Controller
{
    use ApiResponse;
    private SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
    }

    /**
     * Get site information for frontend display
     */
    public function index(): JsonResponse
    {
        $siteInfo = $this->siteInfoService->getSiteInfo();

        return $this->success($siteInfo, 'Site info retrieved successfully');
    }
}
