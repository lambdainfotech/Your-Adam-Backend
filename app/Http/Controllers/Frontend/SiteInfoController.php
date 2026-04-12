<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteInfoService;
use Illuminate\Http\JsonResponse;

class SiteInfoController extends Controller
{
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

        return response()->json([
            'success' => true,
            'data' => $siteInfo,
        ]);
    }
}
