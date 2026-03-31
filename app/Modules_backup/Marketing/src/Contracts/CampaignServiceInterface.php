<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Contracts;

use App\Modules\Marketing\Models\Campaign;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Collection;

interface CampaignServiceInterface
{
    public function getActiveCampaigns(): Collection;

    public function getCampaignDiscount(Product $product, float $price): float;

    public function isProductInCampaign(Product $product, ?Campaign $campaign = null): bool;
}
