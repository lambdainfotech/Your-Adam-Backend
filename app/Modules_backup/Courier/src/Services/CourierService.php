<?php

declare(strict_types=1);

namespace App\Modules\Courier\Services;

use App\Modules\Courier\Contracts\CourierServiceInterface;
use App\Modules\Courier\Models\Courier;
use App\Modules\Courier\Models\CourierAssignment;
use App\Modules\Courier\Repositories\AssignmentRepository;
use App\Modules\Courier\Repositories\CourierRepository;
use App\Modules\Courier\Repositories\TrackingRepository;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Services\BaseService;
use Illuminate\Support\Collection;

class CourierService extends BaseService implements CourierServiceInterface
{
    public function __construct(
        private CourierRepository $courierRepo,
        private AssignmentRepository $assignmentRepo,
        private TrackingRepository $trackingRepo
    ) {
    }

    public function getActiveCouriers(): Collection
    {
        return $this->courierRepo->getActive();
    }

    public function assignCourier(
        int $orderId,
        int $courierId,
        ?string $trackingNumber = null,
        ?float $shippingCost = null
    ): CourierAssignment {
        return $this->transaction(function () use ($orderId, $courierId, $trackingNumber, $shippingCost) {
            $courier = $this->courierRepo->find($courierId);
            $trackingUrl = $trackingNumber ? $this->generateTrackingUrl($courier, $trackingNumber) : null;

            $assignment = $this->assignmentRepo->create([
                'order_id' => $orderId,
                'courier_id' => $courierId,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
                'assigned_at' => now(),
                'shipping_cost' => $shippingCost,
                'created_by' => auth()->id(),
            ]);

            // Update order with courier info
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => OrderStatus::SHIPPED]);
            }

            // Create tracking history entry
            $this->trackingRepo->create([
                'order_id' => $orderId,
                'status' => 'shipped',
                'description' => "Order assigned to {$courier->name}",
                'tracked_at' => now(),
            ]);

            return $assignment;
        });
    }

    public function updateTracking(
        int $orderId,
        string $status,
        ?string $location = null,
        ?string $description = null
    ): void {
        $this->trackingRepo->create([
            'order_id' => $orderId,
            'status' => $status,
            'location' => $location,
            'description' => $description,
            'tracked_at' => now(),
        ]);
    }

    public function getTrackingHistory(int $orderId): Collection
    {
        return $this->trackingRepo->getByOrderId($orderId);
    }

    public function generateTrackingUrl(Courier $courier, string $trackingNumber): string
    {
        if (empty($courier->tracking_url_template)) {
            return '';
        }

        return str_replace('{tracking_number}', urlencode($trackingNumber), $courier->tracking_url_template);
    }
}
