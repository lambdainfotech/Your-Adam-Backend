<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use ApiResponse;
    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Get reviews for a product
     */
    public function index(int $productId, Request $request): JsonResponse
    {
        $reviews = $this->reviewService->getProductReviews($productId, $request);

        return $this->success($reviews, 'Reviews retrieved successfully');
    }

    /**
     * Create a new review
     */
    public function store(int $productId, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->error('Authentication required', 401);
        }

        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:10|max:2000',
            'images' => 'nullable|array',
            'images.*' => 'url',
        ]);

        $result = $this->reviewService->createReview($productId, $user->id, $request->all());

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['review'], 'Review submitted successfully');
    }

    /**
     * Mark review as helpful
     */
    public function helpful(int $reviewId): JsonResponse
    {
        $result = $this->reviewService->markHelpful($reviewId);

        if (!$result['success']) {
            return $this->error($result['message'], 404);
        }

        return $this->success(['helpfulCount' => $result['helpfulCount']], 'Review marked as helpful');
    }
}
