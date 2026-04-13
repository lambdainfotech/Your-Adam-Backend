@extends('admin.layouts.master')

@section('title', 'POS Order #' . $order->order_number)
@section('page-title', 'POS Order Details')

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
            <a href="{{ route('admin.pos.order.print', $order->id) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-print mr-2"></i>Print
            </a>
            <a href="{{ route('admin.pos.order.receipt', $order->id) }}" target="_blank" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <i class="fas fa-receipt mr-2"></i>Receipt
            </a>
        </div>
    </div>

    <!-- Order Info Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Order Information</h3>
                <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">
                    <i class="fas fa-cash-register mr-1"></i>POS
                </span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Order Number</span>
                    <span class="font-semibold text-gray-800">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Date</span>
                    <span class="font-semibold text-gray-800">{{ $order->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                @if($order->is_wholesale)
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                        Wholesale
                    </span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Session ID</span>
                    <span class="font-semibold text-gray-800">#{{ $order->pos_session_id }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $order->customer_name ?? $order->user->name ?? 'Walk-in Customer' }}</p>
                        @if($order->customer_phone)
                            <p class="text-sm text-gray-500">{{ $order->customer_phone }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cashier Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cashier Information</h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $order->user->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">{{ $order->user->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Unit Price</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $item->product_name }}</p>
                                    @if($item->variant_info)
                                        <p class="text-sm text-gray-500">{{ $item->variant_info }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->sku ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-gray-100 rounded text-sm font-medium">{{ $item->quantity }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-600">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800">৳{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Info & Totals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Details -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h3>
            <div class="space-y-4">
                @foreach($order->payments as $payment)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <i class="fas fa-{{ $payment->payment_method === 'cash' ? 'money-bill-wave' : ($payment->payment_method === 'card' ? 'credit-card' : 'mobile-alt') }}"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ ucfirst($payment->payment_method) }}</p>
                            @if($payment->reference_number)
                                <p class="text-sm text-gray-500">Ref: {{ $payment->reference_number }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="font-bold text-gray-800">৳{{ number_format($payment->amount, 2) }}</span>
                </div>
                @if($payment->received_amount)
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Received:</span>
                        <span class="font-medium">৳{{ number_format($payment->received_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Change:</span>
                        <span class="font-medium text-green-600">৳{{ number_format($payment->change_amount ?? 0, 2) }}</span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->tax_amount > 0)
                <div class="flex justify-between text-gray-600">
                    <span>Tax</span>
                    <span>৳{{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="border-t pt-3 mt-3">
                    <div class="flex justify-between">
                        <span class="text-lg font-bold text-gray-800">Total</span>
                        <span class="text-2xl font-bold text-purple-600">৳{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery & Tracking -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Delivery & Tracking</h3>
        </div>
        <div class="p-6">
            <!-- Current Status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Current Status</h4>
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $order->delivery_status_badge_class }}">
                            {{ ucfirst($order->delivery_status) }}
                        </span>
                    </div>
                    @if($order->tracking_number)
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tracking Number:</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $order->tracking_number }}</span>
                            </div>
                        </div>
                    @endif
                    @if($order->courier)
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-gray-500">Courier:</span>
                            <span class="font-semibold text-gray-800">{{ $order->courier->name }}</span>
                        </div>
                    @endif
                    @if($order->estimated_delivery_date)
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-gray-500">Estimated Delivery:</span>
                            <span class="font-semibold text-gray-800">{{ $order->estimated_delivery_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($order->delivered_at)
                        <div class="flex justify-between text-sm mt-2">
                            <span class="text-gray-500">Delivered At:</span>
                            <span class="font-semibold text-green-600">{{ $order->delivered_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Update Status Form -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Update Status</h4>
                    <form action="{{ route('admin.pos.order.delivery-status', $order->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Status</label>
                                <select name="delivery_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="pending" {{ $order->delivery_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->delivery_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="ready" {{ $order->delivery_status === 'ready' ? 'selected' : '' }}>Ready for Pickup</option>
                                    <option value="shipped" {{ $order->delivery_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->delivery_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->delivery_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Number</label>
                                <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                    placeholder="Enter tracking number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" rows="2" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                    placeholder="Add status update notes..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                <i class="fas fa-sync-alt mr-2"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status History Timeline -->
            @if($order->statusHistory->count() > 0)
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Status History</h4>
                <div class="space-y-4">
                    @foreach($order->statusHistory as $history)
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 flex-shrink-0">
                            <i class="fas fa-history text-xs"></i>
                        </div>
                        <div class="flex-1 bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-gray-800">
                                    {{ ucfirst($history->status) }}
                                    @if($history->previous_status)
                                        <span class="text-gray-400 text-sm">(from {{ ucfirst($history->previous_status) }})</span>
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">{{ $history->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            @if($history->notes)
                                <p class="text-sm text-gray-600">{{ $history->notes }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">By: {{ $history->changedBy?->name ?? 'System' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Note -->
    @if($order->note)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
        <h3 class="text-sm font-semibold text-yellow-800 mb-2">
            <i class="fas fa-sticky-note mr-2"></i>Order Note
        </h3>
        <p class="text-yellow-700">{{ $order->note }}</p>
    </div>
    @endif
</div>
@endsection
