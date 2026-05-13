<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\SiteInfoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AboutPageController extends Controller
{
    use ApiResponse;

    private SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
    }

    /**
     * Get about page data for frontend display
     */
    public function index(): JsonResponse
    {
        $siteInfo = $this->siteInfoService->getSiteInfo();

        $team = [];
        if (($siteInfo['aboutPage']['showTeam'] ?? true)) {
            $team = TeamMember::active()
                ->ordered()
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'designation' => $member->designation,
                        'bio' => $member->bio,
                        'photo' => $member->photo,
                        'email' => $member->email,
                        'linkedin' => $member->linkedin,
                        'twitter' => $member->twitter,
                    ];
                });
        }

        return $this->success([
            'page' => $siteInfo['aboutPage'] ?? [],
            'team' => $team,
            'contact' => $siteInfo['contact'] ?? [],
        ], 'About page data retrieved successfully');
    }
}
