<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Services\SiteInfoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactPageController extends Controller
{
    use ApiResponse;

    private SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
    }

    /**
     * Get contact page data for frontend display
     */
    public function index(): JsonResponse
    {
        $siteInfo = $this->siteInfoService->getSiteInfo();

        return $this->success([
            'page' => $siteInfo['contactPage'] ?? [],
            'contact' => $siteInfo['contact'] ?? [],
            'social' => $siteInfo['social'] ?? [],
        ], 'Contact page data retrieved successfully');
    }

    /**
     * Submit contact form
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        $submission = ContactSubmission::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success([
            'id' => $submission->id,
            'message' => 'Thank you for contacting us. We will get back to you soon.',
        ], 'Contact form submitted successfully');
    }
}
