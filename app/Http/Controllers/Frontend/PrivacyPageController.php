<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\SiteInfoService;

class PrivacyPageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SiteInfoService $siteInfoService
    ) {}

    public function index()
    {
        $settings = $this->siteInfoService->getSiteInfo();

        return $this->success(
            [
                'page' => $settings['privacyPage'],
            ],
            'Privacy Policy page content retrieved successfully'
        );
    }
}
