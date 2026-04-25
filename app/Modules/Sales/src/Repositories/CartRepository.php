<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Modules\Sales\Models\Cart;

class CartRepository
{
    public function __construct(
        protected Cart $model,
    ) {}

    public function findByUserId(int $userId): ?Cart
    {
        return $this->model
            ->with('items.variant.product')
            ->where('user_id', $userId)
            ->first();
    }

    public function findOrCreateByUserId(int $userId): Cart
    {
        $cart = $this->findByUserId($userId);

        if ($cart === null) {
            $cart = $this->model->create([
                'user_id' => $userId,
            ]);
            $cart->load('items.variant.product');
        }

        return $cart;
    }

    public function findById(int $cartId): ?Cart
    {
        return $this->model
            ->with('items.variant.product')
            ->find($cartId);
    }

    public function update(int $cartId, array $data): bool
    {
        return $this->model
            ->where('id', $cartId)
            ->update($data);
    }

    public function delete(int $cartId): bool
    {
        return $this->model
            ->where('id', $cartId)
            ->delete();
    }
}
