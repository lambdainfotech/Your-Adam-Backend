<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PosSession;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosPayment;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $session = PosSession::active()->byUser(Auth::id())->firstOrFail();

        return DB::transaction(function () use ($validated, $session) {
            $order = PosOrder::create([
                'pos_session_id' => $session->id,
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
            $this->processPayments($order, $validated['payments'], $session);

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
     * Process payments and update session sales totals.
     */
    protected function processPayments(PosOrder $order, array $payments, PosSession $session): void
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

            $this->updateSessionSales($session, $payment['method'], $payment['amount']);
        }
    }

    /**
     * Update session sales totals based on payment method.
     */
    protected function updateSessionSales(PosSession $session, string $method, float $amount): void
    {
        switch ($method) {
            case 'cash':
                $session->increment('cash_sales', $amount);
                break;
            case 'card':
                $session->increment('card_sales', $amount);
                break;
            case 'bkash':
            case 'nagad':
                $session->increment('mobile_sales', $amount);
                break;
            default:
                $session->increment('other_sales', $amount);
        }
    }
}
