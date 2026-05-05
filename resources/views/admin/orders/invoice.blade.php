<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Actions -->
        <div class="no-print bg-gray-50 px-8 py-4 border-b flex justify-between items-center">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Order
            </a>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Print Invoice
            </button>
        </div>
        
        <!-- Invoice Header -->
        <div class="p-8 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
                    <p class="text-gray-500 mt-1">#{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center text-white text-2xl font-bold mx-auto">
                        E
                    </div>
                    <p class="font-semibold text-gray-800 mt-2">E-Commerce Store</p>
                    <p class="text-sm text-gray-500">support@example.com</p>
                </div>
            </div>
        </div>
        
        <!-- Invoice Info -->
        <div class="p-8 border-b">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Bill To</h3>
                    @php
                        $customer = $order->customer_type === 'guest' ? $order->guest : $order->user;
                    @endphp
                    <p class="font-medium text-gray-800">{{ $customer?->name ?? 'Guest' }}</p>
                    <p class="text-gray-600">{{ $customer?->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $customer?->phone ?? ($customer?->mobile ?? 'N/A') }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Order Details</h3>
                    <p class="text-gray-600">Order Date: <span class="font-medium text-gray-800">{{ $order->created_at->format('M d, Y') }}</span></p>
                    <p class="text-gray-600">Status: 
                        <span class="px-2 py-1 text-xs rounded-full inline-block mt-1
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        ">{{ ucfirst($order->status) }}</span>
                    </p>
                </div>
            </div>
            
            @if($order->delivery_address)
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Shipping Address</h3>
                    <address class="not-italic text-gray-600">
                        @php
                            $addrLine1 = $order->delivery_address['address_line_1'] ?? $order->delivery_address['address'] ?? '';
                            $addrLine2 = $order->delivery_address['address_line_2'] ?? '';
                            $state = $order->delivery_address['state'] ?? $order->delivery_address['district'] ?? '';
                            $postcode = $order->delivery_address['postal_code'] ?? $order->delivery_address['postcode'] ?? '';
                        @endphp
                        {{ $order->delivery_address['name'] ?? 'N/A' }}<br>
                        {{ $addrLine1 }}<br>
                        @if($addrLine2)
                            {{ $addrLine2 }}<br>
                        @endif
                        {{ $order->delivery_address['city'] ?? '' }}, {{ $state }} {{ $postcode }}<br>
                        {{ $order->delivery_address['country'] ?? '' }}
                    </address>
                </div>
            @endif
        </div>
        
        <!-- Order Items -->
        <div class="p-8">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Item</th>
                        <th class="text-center py-3 text-sm font-semibold text-gray-600">Quantity</th>
                        <th class="text-right py-3 text-sm font-semibold text-gray-600">Unit Price</th>
                        <th class="text-right py-3 text-sm font-semibold text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $imageUrl = $item->variant->mainImage?->full_image_url
                                ?? $item->variant->product?->mainImage?->full_image_url
                                ?? null;
                        @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $item->variant->product->name ?? 'Product' }}" class="w-12 h-12 object-cover rounded border border-gray-200">
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $item->variant->product->name ?? 'Unknown Product' }}</p>
                                        <p class="text-sm text-gray-500">SKU: {{ $item->variant->sku ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-center">{{ $item->quantity }}</td>
                            <td class="py-4 text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-right font-medium">৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Totals -->
            <div class="mt-8 flex justify-end">
                <div class="w-full max-w-sm space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal:</span>
                        <span>৳{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-gray-600">
                        <span>Tax:</span>
                        <span>৳{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping:</span>
                        <span>
                            @if($order->shipping_amount > 0)
                                ৳{{ number_format($order->shipping_amount, 2) }}
                            @else
                                <span class="text-green-600">Free</span>
                            @endif
                        </span>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between text-xl font-bold text-gray-800">
                            <span>Total:</span>
                            <span class="text-blue-600">৳{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 p-8 text-center border-t">
            <p class="text-gray-500 text-sm">Thank you for your business!</p>
            <p class="text-gray-400 text-xs mt-1">For any questions, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
