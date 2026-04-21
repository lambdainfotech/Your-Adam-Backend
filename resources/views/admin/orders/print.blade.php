<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
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
        .invoice-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .invoice-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 40px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .brand-logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #3b82f6;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 700;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .invoice-title p {
            font-size: 14px;
            opacity: 0.9;
        }
        .header-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        .info-block p {
            font-size: 14px;
            font-weight: 600;
        }
        .invoice-body {
            padding: 40px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .customer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .info-card h4 {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .info-card p {
            color: #111827;
            font-weight: 600;
            margin: 5px 0;
        }
        .info-card .small {
            font-size: 12px;
            color: #6b7280;
            font-weight: 400;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            text-align: left;
            padding: 12px;
            background: #f3f4f6;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .product-icon {
            width: 40px;
            height: 40px;
            background: #eff6ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
        }
        .product-details h4 {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }
        .product-details p {
            font-size: 11px;
            color: #6b7280;
        }
        .text-right {
            text-align: right;
        }
        .qty-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #f3f4f6;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .totals-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 14px;
        }
        .total-label {
            color: #6b7280;
        }
        .total-value {
            color: #111827;
            font-weight: 500;
        }
        .discount {
            color: #10b981;
        }
        .grand-total {
            border-top: 2px solid #e5e7eb;
            margin-top: 15px;
            padding-top: 15px;
        }
        .grand-total .total-label {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .grand-total .total-value {
            font-size: 20px;
            font-weight: 700;
            color: #3b82f6;
        }
        .payment-info {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px dashed #e5e7eb;
        }
        .payment-method {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .payment-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #3b82f6;
        }
        .payment-details h4 {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .payment-details p {
            font-size: 18px;
            font-weight: 700;
            color: #1d4ed8;
        }
        .invoice-footer {
            background: #f8f9fa;
            padding: 30px 40px;
            text-align: center;
        }
        .thank-you {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .footer-text {
            color: #6b7280;
            font-size: 13px;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 12px;
            color: #6b7280;
        }
        .contact-info span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .print-btn:hover {
            transform: translateY(-2px);
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
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
        <i class="fas fa-print"></i> Print Invoice
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-top">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="brand-name">{{ config('app.name', 'Laravel') }}</div>
                </div>
                <div class="invoice-title">
                    <h1>INVOICE</h1>
                    <p>#{{ $order->order_number }}</p>
                </div>
            </div>
            <div class="header-info">
                <div class="info-block">
                    <h3>Order Date</h3>
                    <p>{{ $order->created_at->format('F d, Y') }}</p>
                </div>
                <div class="info-block">
                    <h3>Order Status</h3>
                    <p>{{ ucfirst($order->status) }}</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Customer Info -->
            <div class="customer-info">
                <div class="info-card">
                    <h4>Bill To</h4>
                    <p>{{ $order->user->name ?? 'Guest' }}</p>
                    <p class="small">{{ $order->user->email ?? 'N/A' }}</p>
                    <p class="small">{{ $order->user->mobile ?? 'N/A' }}</p>
                </div>
                <div class="info-card">
                    <h4>Shipping Address</h4>
                    @if($order->delivery_address)
                        @php
                            $addrLine1 = $order->delivery_address['address_line_1'] ?? $order->delivery_address['address'] ?? '';
                            $addrLine2 = $order->delivery_address['address_line_2'] ?? '';
                            $state = $order->delivery_address['state'] ?? $order->delivery_address['district'] ?? '';
                            $postcode = $order->delivery_address['postal_code'] ?? $order->delivery_address['postcode'] ?? '';
                        @endphp
                        <p>{{ $order->delivery_address['name'] ?? 'N/A' }}</p>
                        <p class="small">{{ $addrLine1 }}</p>
                        @if($addrLine2)
                            <p class="small">{{ $addrLine2 }}</p>
                        @endif
                        <p class="small">{{ $order->delivery_address['city'] ?? '' }}, {{ $state }} {{ $postcode }}</p>
                    @else
                        <p class="small">No shipping address</p>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="section-title">Order Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-info">
                                <div class="product-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="product-details">
                                    <h4>{{ $item->variant->product->name ?? 'Unknown Product' }}</h4>
                                    <p>SKU: {{ $item->variant->sku ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="qty-badge">{{ $item->quantity }}</span>
                        </td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right" style="font-weight: 600;">৳{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-row">
                    <span class="total-label">Discount</span>
                    <span class="total-value discount">-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="total-row">
                    <span class="total-label">Tax</span>
                    <span class="total-value">৳{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Shipping</span>
                    <span class="total-value">৳{{ number_format($order->shipping_amount, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span class="total-label">TOTAL</span>
                    <span class="total-value">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Payment -->
            <div class="payment-info">
                <div class="section-title">Payment Information</div>
                <div class="payment-method">
                    <div class="payment-icon">
                        <i class="fas fa-{{ $order->payment_method === 'cash' ? 'money-bill-wave' : ($order->payment_method === 'card' ? 'credit-card' : 'mobile-alt') }}"></i>
                    </div>
                    <div class="payment-details">
                        <h4>Payment Method</h4>
                        <p>{{ ucfirst($order->payment_method ?? 'Unknown') }}</p>
                    </div>
                    <div style="margin-left: auto; text-align: right;">
                        <h4>Payment Status</h4>
                        <p style="color: {{ $order->payment_status === 'paid' ? '#10b981' : '#f59e0b' }};">
                            {{ ucfirst($order->payment_status ?? 'Pending') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="thank-you">Thank You For Your Purchase!</div>
            <p class="footer-text">We appreciate your business and hope you enjoy your products.</p>
            <div class="contact-info">
                <span><i class="fas fa-envelope"></i> support@example.com</span>
                <span><i class="fas fa-phone"></i> +880 1XXX-XXXXXX</span>
                <span><i class="fas fa-globe"></i> {{ config('app.url') }}</span>
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
