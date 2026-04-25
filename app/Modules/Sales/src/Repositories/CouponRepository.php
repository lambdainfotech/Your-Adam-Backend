<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Coupon;
use App\Modules\Sales\Models\CouponUsage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository
{
    public function __construct(
        protected Coupon $model,
        protected CouponUsage $usageModel,
    ) {}

    public function find(int $id): ?Coupon
    {
        return $this->model->find($id);
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->model
            ->where('code', $code)
            ->first();
    }

    public function create(array $data): Coupon
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model
            ->where('id', $id)
            ->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model
            ->where('id', $id)
            ->delete();
    }

    public function getActive(): LengthAwarePaginator
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function incrementUsage(int $couponId): bool
    {
        return $this->model
            ->where('id', $couponId)
            ->increment('usage_count');
    }

    public function recordUsage(int $couponId, int $userId, int $orderId, float $discountAmount): void
    {
        $this->usageModel->create([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        $this->incrementUsage($couponId);
    }

    public function getUsageCountByUser(int $couponId, int $userId): int
    {
        return $this->usageModel
            ->where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->count();
    }
}
