<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\NavigationService;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
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

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
