<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\NavigationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
    use ApiResponse;
    private NavigationService $navigationService;

    public function __construct(NavigationService $navigationService)
    {
        $this->navigationService = $navigationService;
    }

    /**
     * Get navigation data (header and footer)
     */
    public function index(): JsonResponse
    {
        $data = $this->navigationService->getNavigation();

        return $this->success($data, 'Navigation data retrieved successfully');
    }
}
