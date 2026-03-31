<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Contracts\CampaignServiceInterface;
use App\Modules\Marketing\Models\Campaign;
use App\Modules\Marketing\Repositories\CampaignRepository;
use App\Modules\Product\Models\Product;
use App\Modules\Shared\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CampaignService extends BaseService implements CampaignServiceInterface
{
    public function __construct(
        private CampaignRepository $repository
    ) {
    }

    public function getActiveCampaigns(): Collection
    {
        return Cache::remember('campaigns:active', 3600, function () {
            return $this->repository->getActive();
        });
    }

    public function getCampaignDiscount(Product $product, float $price): float
    {
        $campaigns = $this->getActiveCampaigns();
        $maxDiscount = 0;

        foreach ($campaigns as $campaign) {
            if ($campaign->apply_to_all || $this->isProductInCampaign($product, $campaign)) {
                $discount = $campaign->discount_type === 'percentage'
                    ? $price * $campaign->discount_value / 100
                    : $campaign->discount_value;

                if ($campaign->max_discount_amount) {
                    $discount = min($discount, $campaign->max_discount_amount);
                }

                $maxDiscount = max($maxDiscount, $discount);
            }
        }

        return $maxDiscount;
    }

    public function isProductInCampaign(Product $product, ?Campaign $campaign = null): bool
    {
        if ($campaign === null) {
            $campaigns = $this->getActiveCampaigns();

            return $campaigns->contains(function ($c) use ($product) {
                return $c->products->contains($product);
            });
        }

        return $campaign->products->contains($product);
    }

    public function clearActiveCampaignsCache(): void
    {
        Cache::forget('campaigns:active');
    }

    public function calculateFinalPrice(Product $product, float $price): float
    {
        $discount = $this->getCampaignDiscount($product, $price);
        $finalPrice = max(0, $price - $discount);

        if ($finalPrice < 0) {
            return 0;
        }

        return $finalPrice;
    }
}
