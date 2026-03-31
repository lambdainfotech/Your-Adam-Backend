<?php

declare(strict_types=1);

namespace App\Modules\Courier\Contracts;

use App\Modules\Courier\Models\Courier;
use App\Modules\Courier\Models\CourierAssignment;
use Illuminate\Support\Collection;

interface CourierServiceInterface
{
    public function getActiveCouriers(): Collection;

    public function assignCourier(
        int $orderId,
        int $courierId,
        ?string $trackingNumber = null,
        ?float $shippingCost = null
    ): CourierAssignment;

    public function updateTracking(
        int $orderId,
        string $status,
        ?string $location = null,
        ?string $description = null
    ): void;

    public function getTrackingHistory(int $orderId): Collection;

    public function generateTrackingUrl(Courier $courier, string $trackingNumber): string;
}
