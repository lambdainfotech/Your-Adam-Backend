<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Product;
use App\Services\ProductApiTransformer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    use ApiResponse;
    protected ProductApiTransformer $productTransformer;

    public function __construct(ProductApiTransformer $productTransformer)
    {
        $this->productTransformer = $productTransformer;
    }

    /**
     * List campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::query();

        // Filter by status
        $status = $request->input('status', 'active');
        match ($status) {
            'active' => $query->active(),
            'upcoming' => $query->upcoming(),
            'ended' => $query->ended(),
            default => $query->active(),
        };

        // Filter by apply_type
        if ($request->filled('apply_type')) {
            $query->where('apply_type', $request->apply_type);
        }

        $campaigns = $query->orderBy('starts_at', 'desc')
            ->paginate($request->input('per_page', 10));

        $campaigns->getCollection()->transform(function ($campaign) {
            return $this->transformCampaign($campaign);
        });

        return $this->paginated($campaigns, 'Campaigns retrieved successfully');
    }

    /**
     * Get single campaign with products
     */
    public function show(string $slug): JsonResponse
    {
        $campaign = Campaign::where('slug', $slug)
            ->with([
                'categories' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->firstOrFail();

        // Load products based on campaign apply type
        if ($campaign->apply_to_all || $campaign->apply_type === 'all') {
            $products = Product::where('is_active', true)
                ->with(['category', 'mainImage', 'variants.attributeValues.attribute', 'variants.mainImage'])
                ->get();
            $campaign->setRelation('products', $products);
        } elseif ($campaign->apply_type === 'categories') {
            $categoryIds = $campaign->categories->pluck('id');
            if ($categoryIds->isNotEmpty()) {
                $products = Product::where('is_active', true)
                    ->where(function ($query) use ($categoryIds) {
                        $query->whereIn('category_id', $categoryIds)
                            ->orWhereHas('categories', function ($q) use ($categoryIds) {
                                $q->whereIn('categories.id', $categoryIds);
                            });
                    })
                    ->with(['category', 'mainImage', 'variants.attributeValues.attribute', 'variants.mainImage'])
                    ->get();
            } else {
                $products = collect();
            }
            $campaign->setRelation('products', $products);
        } else {
            // Explicitly attached products
            $products = $campaign->products()
                ->where('is_active', true)
                ->with(['category', 'mainImage', 'variants.attributeValues.attribute', 'variants.mainImage'])
                ->get();
            $campaign->setRelation('products', $products);
        }

        $data = $this->transformCampaign($campaign, true);

        return $this->success($data, 'Campaign retrieved successfully');
    }

    /**
     * Get featured / currently running campaigns
     */
    public function featured(): JsonResponse
    {
        $campaigns = Campaign::active()
            ->orderBy('starts_at', 'desc')
            ->limit(5)
            ->get();

        return $this->success([
            'campaigns' => $campaigns->map(fn ($c) => $this->transformCampaign($c)),
        ], 'Featured campaigns retrieved successfully');
    }

    /**
     * Transform campaign to API format
     */
    private function transformCampaign(Campaign $campaign, bool $withProducts = false): array
    {
        $data = [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'slug' => $campaign->slug,
            'description' => $campaign->description,
            'banner_image' => $campaign->banner_image_url,
            'discount' => [
                'type' => $campaign->discount_type,
                'value' => (float) $campaign->discount_value,
            ],
            'conditions' => [
                'min_purchase_amount' => (float) $campaign->min_purchase_amount,
                'max_discount_amount' => $campaign->max_discount_amount ? (float) $campaign->max_discount_amount : null,
            ],
            'schedule' => [
                'starts_at' => $campaign->starts_at?->toDateTimeString(),
                'ends_at' => $campaign->ends_at?->toDateTimeString(),
                'is_running' => $campaign->is_running,
                'is_expired' => $campaign->is_expired,
            ],
            'apply' => [
                'type' => $campaign->apply_type,
                'to_all' => $campaign->apply_to_all,
            ],
            'timestamps' => [
                'created_at' => $campaign->created_at?->toDateTimeString(),
                'updated_at' => $campaign->updated_at?->toDateTimeString(),
            ],
        ];

        if ($withProducts && $campaign->relationLoaded('products')) {
            $data['products'] = $campaign->products->map(function ($product) use ($campaign) {
                $transformed = $this->productTransformer->transform($product);

                // Add campaign-specific pricing if special_price exists
                $pivot = $product->pivot;
                if ($pivot && $pivot->special_price) {
                    $transformed['campaign_price'] = (float) $pivot->special_price;
                    $transformed['campaign_savings'] = round($transformed['pricing']['final_price'] - $pivot->special_price, 2);
                }

                return $transformed;
            });
        }

        if ($withProducts && $campaign->relationLoaded('categories')) {
            $data['categories'] = $campaign->categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]);
        }

        return $data;
    }
}
