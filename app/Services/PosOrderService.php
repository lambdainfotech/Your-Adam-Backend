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
        return DB::transaction(function () use ($validated) {
            $orderData = [
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
            ];

            // Handle legacy pos_session_id column on older databases
            if (Schema::hasColumn('pos_orders', 'pos_session_id')) {
                $orderData['pos_session_id'] = 0;
            }

            $order = PosOrder::create($orderData);

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
