<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .flex { display: flex; }
        .flex-1 { flex: 1; }
        .justify-between { justify-content: space-between; }
        .store-name {
            font-size: 16px;
            font-weight: bold;
        }
        .store-info {
            font-size: 10px;
            color: #555;
        }
        .order-info {
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
        }
        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .totals {
            margin-top: 10px;
            padding-top: 10px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .total-final {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            margin-top: 5px;
        }
        .payment-info {
            margin-top: 10px;
            padding-top: 10px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
        }
        .barcode {
            text-align: center;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            letter-spacing: 2px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Store Info -->
    <div class="text-center mb-2">
        <div class="store-name">{{ config('app.name', 'E-Commerce Store') }}</div>
        <div class="store-info mt-1">
            <div>POS Receipt</div>
        </div>
    </div>

    <!-- Order Info -->
    <div class="order-info border-top border-bottom mt-2 mb-2 pt-2 pb-2">
        <div class="flex justify-between">
            <span>Order:</span>
            <span class="bold">{{ $order->order_number }}</span>
        </div>
        <div class="flex justify-between">
            <span>Date:</span>
            <span>{{ $order->created_at->format('Y-m-d H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Cashier:</span>
            <span>{{ $order->user?->name ?? 'Unknown' }}</span>
        </div>
        @if($order->customer_name)
        <div class="flex justify-between">
            <span>Customer:</span>
            <span>{{ $order->customer_name }}</span>
        </div>
        @endif
        @if($order->is_wholesale)
        <div class="flex justify-between">
            <span>Type:</span>
            <span class="bold">WHOLESALE</span>
        </div>
        @endif
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product_name }}
                    @if($item->variant_info)
                        <br><small>{{ $item->variant_info }}</small>
                    @endif
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals border-top">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>৳{{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if($order->discount_amount > 0)
        <div class="totals-row">
            <span>Discount:</span>
            <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
        </div>
        @endif
        @if($order->tax_amount > 0)
        <div class="totals-row">
            <span>Tax:</span>
            <span>৳{{ number_format($order->tax_amount, 2) }}</span>
        </div>
        @endif
        <div class="totals-row total-final">
            <span>TOTAL:</span>
            <span>৳{{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="payment-info border-top">
        <div class="bold mb-1">Payment:</div>
        @foreach($order->payments as $payment)
        <div class="totals-row">
            <span>{{ ucfirst($payment->payment_method) }}:</span>
            <span>৳{{ number_format($payment->amount, 2) }}</span>
        </div>
        @if($payment->is_cash)
            <div class="totals-row">
                <span>Received:</span>
                <span>৳{{ number_format($payment->received_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Change:</span>
                <span>৳{{ number_format($payment->change_amount, 2) }}</span>
            </div>
        @endif
        @endforeach
    </div>

    <!-- Barcode -->
    <div class="barcode border-top mt-2 pt-2">
        *{{ $order->order_number }}*
    </div>

    <!-- Footer -->
    <div class="footer border-top">
        <p>Thank you for shopping with us!</p>
        <p class="mt-1">Goods once sold cannot be returned.</p>
        <p class="mt-1">{{ config('app.url') }}</p>
    </div>

    <!-- Print Buttons -->
    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded">
            <i class="fas fa-print mr-1"></i>Print Receipt
        </button>
        <button onclick="window.close()" class="px-4 py-2 bg-gray-600 text-white rounded ml-2">
            <i class="fas fa-times mr-1"></i>Close
        </button>
    </div>

    <script>
        // Auto-print after 500ms
        setTimeout(function() {
            window.print();
        }, 500);
    </script>
</body>
</html>
