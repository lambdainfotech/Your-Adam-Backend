<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\SiteInfoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReturnsPageController extends Controller
{
    use ApiResponse;

    private SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
    }

    /**
     * Get returns page data for frontend display
     */
    public function index(): JsonResponse
    {
        $siteInfo = $this->siteInfoService->getSiteInfo();

        return $this->success([
            'page' => $siteInfo['returnsPage'] ?? [],
            'contact' => $siteInfo['contact'] ?? [],
        ], 'Returns page data retrieved successfully');
    }

    /**
     * Submit return request form
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'items' => 'required|string|max:2000',
            'reason' => 'required|string|max:500',
            'details' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $returnRequest = ReturnRequest::create([
            'order_number' => $request->input('order_number'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'items' => $request->input('items'),
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([
            'id' => $returnRequest->id,
            'message' => 'Your return request has been submitted successfully. We will review it and get back to you soon.',
        ], 'Return request submitted successfully');
    }
}
