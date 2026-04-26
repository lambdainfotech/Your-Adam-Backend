<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewService
{
    /**
     * Get reviews for a product
     */
    public function getProductReviews(int $productId, Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 10);

        $query = Review::with('user:id,name,avatar')
            ->where('product_id', $productId)
            ->where('is_approved', true);

        $total = $query->count();

        $reviews = $query->latest()
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Get rating summary
        $ratingSummary = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($ratingSummary);
        $averageRating = $totalReviews > 0 
            ? round(array_sum(array_map(fn($r, $c) => $r * $c, array_keys($ratingSummary), $ratingSummary)) / $totalReviews, 1)
            : 0;

        return [
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => [
                        'name' => $review->user->name,
                        'avatar' => $review->user->avatar,
                    ],
                    'rating' => (float) $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'images' => $review->images ?? [],
                    'isVerifiedPurchase' => $review->is_verified_purchase,
                    'helpfulCount' => $review->helpful_count,
                    'createdAt' => $review->created_at->format('M d, Y'),
                ];
            }),
            'summary' => [
                'averageRating' => $averageRating,
                'totalReviews' => $totalReviews,
                'ratingBreakdown' => [
                    '5' => $ratingSummary[5.0] ?? 0,
                    '4' => $ratingSummary[4.0] ?? 0,
                    '3' => $ratingSummary[3.0] ?? 0,
                    '2' => $ratingSummary[2.0] ?? 0,
                    '1' => $ratingSummary[1.0] ?? 0,
                ],
            ],
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ];
    }

    /**
     * Create a new review
     */
    public function createReview(int $productId, int $userId, array $data): array
    {
        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReview) {
            return [
                'success' => false,
                'message' => 'You have already reviewed this product',
            ];
        }

        // Check if user purchased this product
        $isVerified = Order::where('user_id', $userId)
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->where('status', 'delivered')
            ->exists();

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'],
            'images' => $data['images'] ?? [],
            'is_verified_purchase' => $isVerified,
            'is_approved' => true, // Auto-approve for now
        ]);

        return [
            'success' => true,
            'review' => [
                'id' => $review->id,
                'rating' => (float) $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'isVerifiedPurchase' => $review->is_verified_purchase,
                'createdAt' => $review->created_at->format('M d, Y'),
            ],
        ];
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(int $reviewId, int $userId): array
    {
        $review = Review::find($reviewId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Review not found'];
        }

        // Check if user already voted
        $existingVote = \DB::table('review_helpful_votes')
            ->where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            return ['success' => false, 'message' => 'You have already marked this review as helpful'];
        }

        \DB::table('review_helpful_votes')->insert([
            'review_id' => $reviewId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $review->increment('helpful_count');

        return [
            'success' => true,
            'helpfulCount' => $review->fresh()->helpful_count,
        ];
    }
}
