<?php

declare(strict_types=1);

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Contracts\AuditServiceInterface;
use App\Modules\Sales\Events\OrderCreated;
use App\Modules\Sales\Events\OrderStatusChanged;

class ActivityLogListener
{
    public function __construct(
        private AuditServiceInterface $auditService
    ) {}

    public function handle($event): void
    {
        if ($event instanceof OrderCreated) {
            $this->auditService->log(
                'create',
                'Order',
                $event->order->id,
                null,
                $event->order->toArray(),
                "Order {$event->order->order_number} created"
            );
        } elseif ($event instanceof OrderStatusChanged) {
            $this->auditService->log(
                'update_status',
                'Order',
                $event->order->id,
                ['status' => $event->previousStatus->value],
                ['status' => $event->order->status->value],
                "Order status changed from {$event->previousStatus->label()} to {$event->order->status->label()}"
            );
        }
    }
}
