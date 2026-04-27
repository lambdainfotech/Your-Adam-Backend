<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosPayment;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosOrderService
{
    /**
     * Create a POS order with items, payments, and stock deduction.
     *
     * @param array $validated Validated request data
     * @return PosOrder
     * @throws \Exception
     */
    public function createOrder(array $validated): PosOrder
    {
        // One-time cleanup: remove legacy pos_session_id column + FK constraint
        // if the previous migration failed to drop it
        if (Schema::hasColumn('pos_orders', 'pos_session_id')) {
            try {
                DB::statement('ALTER TABLE pos_orders DROP FOREIGN KEY pos_orders_pos_session_id_foreign');
            } catch (\Exception $e) {
                // FK may already be dropped or named differently
            }
            try {
                DB::statement('ALTER TABLE pos_orders DROP COLUMN pos_session_id');
            } catch (\Exception $e) {
                // Column may already be dropped
            }
        }

        return DB::transaction(function () use ($validated) {
            $order = PosOrder::create([
                'user_id' => Auth::id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'note' => $validated['note'] ?? null,
                'status' => 'completed',
                'is_wholesale' => $validated['is_wholesale'] ?? false,
            ]);

            $this->createOrderItems($order, $validated['items']);
            $this->processPayments($order, $validated['payments']);

            return $order;
        });
    }

    /**
     * Create order items and deduct stock.
     */
    protected function createOrderItems(PosOrder $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $variant = null;
            $variantInfo = null;

            if (!empty($item['variant_id'])) {
                $variant = Variant::find($item['variant_id']);
                $variantInfo = $variant->variant_name ?? null;
            }

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['variant_id'] ?? null,
                'product_name' => $product->name,
                'sku' => $variant ? ($variant->sku ?? 'N/A') : ($product->sku ?? 'N/A'),
                'variant_info' => $variantInfo,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['price'] * $item['quantity'],
            ]);

            // Deduct stock
            if ($variant) {
                $variant->decrement('stock_quantity', $item['quantity']);
            } else {
                $product->decrement('stock_quantity', $item['quantity']);
            }
        }
    }

    /**
     * Process payments.
     */
    protected function processPayments(PosOrder $order, array $payments): void
    {
        foreach ($payments as $payment) {
            PosPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => $payment['method'],
                'amount' => $payment['amount'],
                'reference_number' => $payment['reference'] ?? null,
                'received_amount' => $payment['received_amount'] ?? null,
                'change_amount' => $payment['change_amount'] ?? null,
            ]);
        }
    }
}
