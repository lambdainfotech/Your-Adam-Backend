<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Enums\OrderStatus;
use App\Modules\Sales\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function __construct(
        protected Order $model,
    ) {}

    public function find(int $id): ?Order
    {
        return $this->model
            ->with(['items.variant.product', 'user', 'statusHistory'])
            ->find($id);
    }

    public function findByNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->with(['items.variant.product', 'user', 'statusHistory'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function findByIdAndUser(int $orderId, int $userId): ?Order
    {
        return $this->model
            ->with(['items.variant.product', 'statusHistory'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model
            ->where('id', $id)
            ->update($data);
    }

    public function listByUserId(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['items' => function ($query) {
                $query->limit(3);
            }])
            ->where('user_id', $userId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function ($uq) use ($filters) {
                        $uq->where('name', 'like', "%{$filters['search']}%")
                            ->orWhere('email', 'like', "%{$filters['search']}%");
                    });
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getOrdersByStatus(OrderStatus $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->get();
    }

    public function addStatusHistory(int $orderId, array $data): void
    {
        $order = $this->find($orderId);
        if ($order) {
            $order->statusHistory()->create($data);
        }
    }
}
