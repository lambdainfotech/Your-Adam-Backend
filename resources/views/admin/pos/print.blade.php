<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .receipt-header .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: #667eea;
        }
        .receipt-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .receipt-header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .receipt-body {
            padding: 25px;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 12px;
        }
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        .info-value {
            color: #111827;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .items-section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-info {
            flex: 1;
        }
        .item-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .item-variant {
            font-size: 11px;
            color: #6b7280;
        }
        .item-sku {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }
        .item-qty {
            color: #6b7280;
            font-size: 12px;
            margin-right: 15px;
        }
        .item-price {
            font-weight: 600;
            color: #111827;
            text-align: right;
        }
        .totals-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 13px;
        }
        .total-label {
            color: #6b7280;
        }
        .total-value {
            color: #111827;
            font-weight: 500;
        }
        .grand-total {
            border-top: 2px solid #e5e7eb;
            margin-top: 12px;
            padding-top: 12px;
        }
        .grand-total .total-label {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }
        .grand-total .total-value {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }
        .payment-section {
            margin-bottom: 20px;
        }
        .payment-method {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .payment-method i {
            color: #10b981;
            font-size: 18px;
        }
        .payment-details {
            flex: 1;
        }
        .payment-name {
            font-weight: 600;
            color: #065f46;
            font-size: 13px;
        }
        .payment-amount {
            font-size: 15px;
            font-weight: 700;
            color: #065f46;
        }
        .barcode-section {
            text-align: center;
            padding: 20px;
            border-top: 2px dashed #e5e7eb;
        }
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            letter-spacing: 3px;
            color: #374151;
            margin-bottom: 10px;
        }
        .barcode-text {
            font-size: 11px;
            color: #6b7280;
        }
        .receipt-footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
        }
        .receipt-footer p {
            color: #6b7280;
            font-size: 12px;
            margin: 5px 0;
        }
        .receipt-footer .thank-you {
            font-size: 16px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
        }
        .social-links {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .social-links a {
            color: #9ca3af;
            font-size: 18px;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }
        .print-btn:hover {
            transform: scale(1.1);
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i>
    </button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="logo">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>POS Order Receipt</p>
        </div>

        <!-- Body -->
        <div class="receipt-body">
            <!-- Order Info -->
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Order Number</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span class="info-value">{{ $order->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cashier</span>
                    <span class="info-value">{{ $order->user?->name ?? 'Unknown' }}</span>
                </div>
                @if($order->customer_name)
                <div class="info-row">
                    <span class="info-label">Customer</span>
                    <span class="info-value">{{ $order->customer_name }}</span>
                </div>
                @endif
                @if($order->customer_phone)
                <div class="info-row">
                    <span class="info-label">Phone</span>
                    <span class="info-value">{{ $order->customer_phone }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
                </div>
                @if($order->is_wholesale)
                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="badge" style="background: #dbeafe; color: #1e40af;">WHOLESALE</span>
                </div>
                @endif
            </div>

            <!-- Items -->
            <div class="items-section">
                <div class="section-title">Order Items</div>
                @foreach($order->items as $item)
                <div class="item">
                    <div class="item-info">
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if($item->variant_info)
                            <div class="item-variant">{{ $item->variant_info }}</div>
                        @endif
                        @if($item->sku)
                            <div class="item-sku">SKU: {{ $item->sku }}</div>
                        @endif
                    </div>
                    <div class="item-qty">×{{ $item->quantity }}</div>
                    <div class="item-price">৳{{ number_format($item->total_price, 2) }}</div>
                </div>
                @endforeach
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-row">
                    <span class="total-label">Discount</span>
                    <span class="total-value" style="color: #10b981;">-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->tax_amount > 0)
                <div class="total-row">
                    <span class="total-label">Tax (5%)</span>
                    <span class="total-value">৳{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span class="total-label">TOTAL</span>
                    <span class="total-value">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Payment -->
            <div class="payment-section">
                <div class="section-title">Payment Details</div>
                @foreach($order->payments as $payment)
                <div class="payment-method">
                    <i class="fas fa-{{ $payment->payment_method === 'cash' ? 'money-bill-wave' : ($payment->payment_method === 'card' ? 'credit-card' : 'mobile-alt') }}"></i>
                    <div class="payment-details">
                        <div class="payment-name">{{ ucfirst($payment->payment_method) }} Payment</div>
                    </div>
                    <div class="payment-amount">৳{{ number_format($payment->amount, 2) }}</div>
                </div>
                @if($payment->received_amount)
                <div class="info-row" style="margin-top: 10px; padding: 0 10px;">
                    <span class="info-label">Received Amount</span>
                    <span class="info-value">৳{{ number_format($payment->received_amount, 2) }}</span>
                </div>
                <div class="info-row" style="padding: 0 10px;">
                    <span class="info-label">Change</span>
                    <span class="info-value" style="color: #10b981;">৳{{ number_format($payment->change_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @endforeach
            </div>

            <!-- Barcode -->
            <div class="barcode-section">
                <div class="barcode">*{{ $order->order_number }}*</div>
                <div class="barcode-text">Scan for order details</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="thank-you">Thank You!</div>
            <p>We appreciate your business</p>
            <p style="margin-top: 10px; font-size: 11px; color: #9ca3af;">Goods once sold cannot be returned</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    </script>
</body>
</html>
