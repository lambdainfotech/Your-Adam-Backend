<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            line-height: 1.45;
            color: #111827;
            background: #f3f4f6;
        }
        body {
            padding: 20px;
        }

        .invoice-wrapper {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Action bar (not printed) */
        .actions {
            background: #f9fafb;
            padding: 14px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .actions a, .actions button {
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-back { color: #2563eb; }
        .btn-back:hover { color: #1d4ed8; }
        .btn-print {
            background: #2563eb;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 500;
        }
        .btn-print:hover { background: #1d4ed8; }

        /* Header */
        .invoice-header {
            padding: 24px 32px 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #e5e7eb;
        }
        .brand-block {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
        }
        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .invoice-title-block {
            text-align: right;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 1px;
        }
        .invoice-number {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Info section */
        .info-section {
            padding: 18px 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-block h4 {
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        .info-block p {
            font-size: 12px;
            color: #111827;
            margin: 2px 0;
        }
        .info-block .name {
            font-weight: 600;
            color: #111827;
            font-size: 13px;
            margin-bottom: 3px;
        }
        .info-block .small {
            color: #6b7280;
            font-size: 11px;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-pill.pending { background: #fef3c7; color: #92400e; }
        .status-pill.completed { background: #d1fae5; color: #065f46; }
        .status-pill.processing { background: #dbeafe; color: #1e40af; }
        .status-pill.cancelled { background: #fee2e2; color: #991b1b; }

        /* Items table */
        .items-section {
            padding: 18px 32px;
        }
        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table thead tr {
            background: #f9fafb;
        }
        .items-table th {
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table th.text-right { text-align: right; }
        .items-table th.text-center { text-align: center; }
        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        .items-table tbody tr:last-child {
            border-bottom: none;
        }
        .items-table td {
            padding: 10px;
            vertical-align: middle;
        }
        .items-table td.text-right { text-align: right; }
        .items-table td.text-center { text-align: center; }
        .product-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .product-image {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            flex-shrink: 0;
        }
        .product-icon-fallback {
            width: 36px;
            height: 36px;
            background: #eff6ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            flex-shrink: 0;
        }
        .product-name {
            font-weight: 600;
            color: #111827;
            font-size: 12px;
            line-height: 1.3;
        }
        .product-sku {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }
        .qty-pill {
            display: inline-block;
            padding: 2px 10px;
            background: #f3f4f6;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
        }

        /* Totals */
        .totals-section {
            padding: 16px 32px 20px;
            display: flex;
            justify-content: flex-end;
        }
        .totals-box {
            width: 100%;
            max-width: 280px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12px;
        }
        .total-row .label { color: #6b7280; }
        .total-row .value { color: #111827; font-weight: 500; }
        .total-row.discount .value { color: #10b981; }
        .total-row.grand {
            border-top: 1.5px solid #111827;
            margin-top: 8px;
            padding-top: 10px;
        }
        .total-row.grand .label {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .total-row.grand .value {
            font-size: 16px;
            font-weight: 700;
            color: #2563eb;
        }

        /* Payment */
        .payment-section {
            padding: 14px 32px;
            border-top: 1px dashed #e5e7eb;
            background: #fafbfc;
        }
        .payment-card {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .payment-icon {
            width: 38px;
            height: 38px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 16px;
        }
        .payment-text .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-text .value {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            text-transform: capitalize;
        }
        .payment-status {
            margin-left: auto;
            text-align: right;
        }

        /* Footer */
        .invoice-footer {
            padding: 16px 32px;
            text-align: center;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .invoice-footer .thanks {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 3px;
        }
        .invoice-footer .small {
            font-size: 11px;
            color: #6b7280;
        }

        /* Print rules */
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .invoice-wrapper {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .actions { display: none !important; }

            /* Page break rules - keep blocks together */
            .invoice-header,
            .info-section,
            .totals-section,
            .payment-section,
            .invoice-footer {
                page-break-inside: avoid;
            }
            /* Allow table to break across pages but keep each row intact */
            .items-table {
                page-break-inside: auto;
            }
            .items-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            .items-table thead {
                display: table-header-group;
            }
            .items-table tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <!-- Action Bar (not printed) -->
        <div class="actions">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Order
            </a>
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>

        @php
            $customer = $order->customer_type === 'guest' ? $order->guest : $order->user;
            $addrLine1 = $order->delivery_address['address_line_1'] ?? $order->delivery_address['address'] ?? '';
            $addrLine2 = $order->delivery_address['address_line_2'] ?? '';
            $state = $order->delivery_address['state'] ?? $order->delivery_address['district'] ?? '';
            $postcode = $order->delivery_address['postal_code'] ?? $order->delivery_address['postcode'] ?? '';
            $statusClass = match($order->status) {
                'completed' => 'completed',
                'processing' => 'processing',
                'cancelled' => 'cancelled',
                default => 'pending',
            };
        @endphp

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="brand-block">
                <div class="brand-logo">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>
                    <div class="brand-name">eCommerce API</div>
                    <div style="font-size: 11px; color: #6b7280;">support@example.com</div>
                </div>
            </div>
            <div class="invoice-title-block">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $order->order_number }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-block">
                <h4>Bill To</h4>
                <p class="name">{{ $customer?->name ?? 'Guest' }}</p>
                <p class="small">{{ $customer?->email ?? 'N/A' }}</p>
                <p class="small">{{ $customer?->phone ?? ($customer?->mobile ?? 'N/A') }}</p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h4>Order Details</h4>
                <p><span class="small">Order Date:</span> <strong>{{ $order->created_at->format('M d, Y') }}</strong></p>
                <p><span class="small">Status:</span> <span class="status-pill {{ $statusClass }}">{{ ucfirst($order->status) }}</span></p>
            </div>
        </div>

        @if($order->delivery_address)
        <div class="info-section" style="border-bottom: 1px solid #e5e7eb;">
            <div class="info-block">
                <h4>Shipping Address</h4>
                <p class="name">{{ $order->delivery_address['name'] ?? 'N/A' }}</p>
                <p class="small">{{ $addrLine1 }}</p>
                @if($addrLine2)
                    <p class="small">{{ $addrLine2 }}</p>
                @endif
                <p class="small">{{ $order->delivery_address['city'] ?? '' }}{{ $state ? ', ' . $state : '' }} {{ $postcode }}</p>
                <p class="small">{{ $order->delivery_address['country'] ?? '' }}</p>
            </div>
            <div class="info-block" style="text-align: right;">
                <h4>Payment</h4>
                <p><strong style="text-transform: capitalize;">{{ ucfirst($order->payment_method ?? 'N/A') }}</strong></p>
                <p class="small">{{ ucfirst($order->payment_status ?? 'Pending') }}</p>
            </div>
        </div>
        @endif

        <!-- Items Table -->
        <div class="items-section">
            <div class="section-label">Order Items ({{ $order->items->count() }})</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Product</th>
                        <th class="text-center" style="width: 12%;">Qty</th>
                        <th class="text-right" style="width: 19%;">Unit Price</th>
                        <th class="text-right" style="width: 19%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    @php
                        $imageUrl = $item->variant?->mainImage?->full_image_url
                            ?? $item->variant?->product?->mainImage?->full_image_url
                            ?? null;
                    @endphp
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->variant?->product?->name ?? 'Product' }}" class="product-image">
                                @else
                                    <div class="product-icon-fallback">
                                        <i class="fas fa-box"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="product-name">{{ $item->variant?->product?->name ?? 'Unknown Product' }}</div>
                                    <div class="product-sku">SKU: {{ $item->variant?->sku ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="qty-pill">{{ $item->quantity }}</span>
                        </td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right" style="font-weight: 600;">৳{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span class="label">Subtotal</span>
                    <span class="value">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="total-row discount">
                    <span class="label">
                        Discount
                        @if($order->coupon_code)
                            <span style="font-size: 10px; color: #9ca3af;">({{ $order->coupon_code }})</span>
                        @endif
                    </span>
                    <span class="value">-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="total-row">
                    <span class="label">Tax</span>
                    <span class="value">৳{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="label">
                        Shipping
                        @if($order->shipping_zone)
                            <span style="font-size: 10px; color: #9ca3af;">({{ $order->shipping_zone === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }})</span>
                        @endif
                    </span>
                    <span class="value">
                        @if($order->shipping_amount > 0)
                            ৳{{ number_format($order->shipping_amount, 2) }}
                        @else
                            <span style="color: #10b981;">Free</span>
                        @endif
                    </span>
                </div>
                <div class="total-row grand">
                    <span class="label">TOTAL</span>
                    <span class="value">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="thanks">Thank you for your business!</div>
            <div class="small">For any questions, please contact our support team.</div>
        </div>
    </div>
</body>
</html>
