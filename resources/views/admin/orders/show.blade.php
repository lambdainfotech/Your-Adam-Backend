@extends('admin.layouts.master')

@section('title', 'Order Details')
@section('page-title', 'Order #' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Orders
            </a>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.orders.print', $order) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700" target="_blank">
                <i class="fas fa-print mr-2"></i>Print Receipt
            </a>
            <a href="{{ route('admin.orders.invoice', $order) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" target="_blank">
                <i class="fas fa-file-invoice mr-2"></i>Invoice
            </a>
        </div>
    </div>
    
    <!-- Order Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Order #{{ $order->order_number }}</h2>
                    <p class="text-gray-500 mt-1">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1.5 text-sm rounded-full
                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $order->payment_status === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}
                    ">{{ ucfirst($order->payment_status) }}</span>
                    <span class="px-3 py-1.5 text-sm rounded-full
                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $order->status === 'delivered' ? 'bg-teal-100 text-teal-800' : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="border-t border-gray-100 pt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Order Items</h3>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        @php
                            $imageUrl = $item->variant->mainImage?->full_image_url
                                ?? $item->variant->product?->mainImage?->full_image_url
                                ?? null;
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-4">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->variant->product->name ?? 'Product' }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $item->variant->product->name ?? 'Unknown Product' }}</p>
                                    <p class="text-sm text-gray-500">SKU: {{ $item->variant->sku ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">৳{{ number_format($item->unit_price, 2) }} each</p>
                                <p class="text-lg font-bold text-blue-600">৳{{ number_format($item->total_price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Order Totals -->
            <div class="border-t border-gray-100 pt-6 mt-6">
                <div class="space-y-2 text-right">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span>৳{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax:</span>
                        <span>৳{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping:</span>
                        <span>
                            @if($order->shipping_amount > 0)
                                ৳{{ number_format($order->shipping_amount, 2) }}
                            @else
                                <span class="text-green-600">Free</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between text-xl font-bold pt-2 border-t">
                        <span>Total:</span>
                        <span class="text-blue-600">৳{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Customer Information</h3>
                <div class="space-y-3">
                    @php
                        $customer = $order->customer_type === 'guest' ? $order->guest : $order->user;
                    @endphp
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $customer?->name ?? 'Guest' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $customer?->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $customer?->phone ?? ($customer?->mobile ?? 'N/A') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Address -->
            @if($order->delivery_address)
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-4">Shipping Address</h3>
                    <address class="not-italic text-gray-600">
                        {{ $order->delivery_address['name'] ?? 'N/A' }}<br>
                        @php
                            $addrLine1 = $order->delivery_address['address_line_1'] ?? $order->delivery_address['address'] ?? '';
                            $addrLine2 = $order->delivery_address['address_line_2'] ?? '';
                            $state = $order->delivery_address['state'] ?? $order->delivery_address['district'] ?? '';
                            $postcode = $order->delivery_address['postal_code'] ?? $order->delivery_address['postcode'] ?? '';
                        @endphp
                        {{ $addrLine1 }}<br>
                        @if($addrLine2)
                            {{ $addrLine2 }}<br>
                        @endif
                        {{ $order->delivery_address['city'] ?? '' }}, {{ $state }} {{ $postcode }}<br>
                        {{ $order->delivery_address['country'] ?? '' }}
                    </address>
                </div>
            @endif
            
            <!-- Payment Info -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Payment Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Method</span>
                        <span class="font-medium uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $order->payment_status === 'refunded' ? 'bg-gray-100 text-gray-800' : '' }}
                        ">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                    @if($order->transaction_id)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Transaction ID</span>
                            <span class="font-medium text-xs">{{ $order->transaction_id }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Update Status -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Update Delivery Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <textarea name="notes" placeholder="Add notes (optional)" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3" rows="2"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Update Payment Status -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Update Payment Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-payment-status', $order) }}">
                    @csrf
                    <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3">
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    <textarea name="notes" placeholder="Add notes (optional)" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3" rows="2"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Update Payment Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
