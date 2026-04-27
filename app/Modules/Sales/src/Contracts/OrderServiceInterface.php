<?php

declare(strict_types=1);

namespace App\Modules\Sales\Contracts;

use App\Modules\Sales\DTOs\CreateOrderDTO;
use App\Modules\Sales\Enums\OrderStatus;
use App\Modules\Sales\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    /**
     * Create a new order from cart.
     */
    public function create(int $userId, CreateOrderDTO $dto): Order;

    /**
     * Create a new order directly from items (bypass cart).
     */
    public function createDirect(int $userId, array $data): Order;

    /**
     * Get order by ID.
     */
    public function getById(int $userId, int $orderId): Order;

    /**
     * Get order by order number.
     */
    public function getByNumber(string $orderNumber): ?Order;

    /**
     * List orders for user.
     */
    public function list(int $userId, array $filters = []): LengthAwarePaginator;

    /**
     * Update order status.
     */
    public function updateStatus(int $orderId, OrderStatus $status, ?string $notes = null): Order;

    /**
     * Cancel order.
     */
    public function cancel(int $userId, int $orderId, ?string $reason = null): Order;

    /**
     * Get order tracking information.
     */
    public function getTracking(string $orderNumber): array;
}
