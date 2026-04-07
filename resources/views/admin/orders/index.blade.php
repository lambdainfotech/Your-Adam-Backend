@extends('admin.layouts.master')

@section('title', 'Orders')
@section('page-title', 'Orders Management')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col md:flex-row gap-4">
            <select name="status" class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="From Date">
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-4 py-2 border border-gray-300 rounded-lg" placeholder="To Date">
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                <a href="{{ route('admin.orders.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-center">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            @endif
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Order #</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Delivery</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">
                                @if($order['type'] === 'pos')
                                    <span class="text-purple-600">#{{ $order['order_number'] }}</span>
                                @else
                                    <a href="{{ route('admin.orders.show', $order['id']) }}" class="text-blue-600 hover:underline">#{{ $order['order_number'] }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($order['type'] === 'pos')
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        <i class="fas fa-cash-register mr-1"></i>POS
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-globe mr-1"></i>Website
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-800">{{ $order['customer_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium">৳{{ number_format($order['total'], 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $order['status'] === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $order['status'] === 'delivered' ? 'bg-teal-100 text-teal-800' : '' }}
                                    {{ $order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                ">{{ ucfirst($order['status']) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($order['type'] === 'pos')
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $order['delivery_status'] === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order['delivery_status'] === 'shipped' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $order['delivery_status'] === 'ready' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $order['delivery_status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order['delivery_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order['delivery_status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                    ">{{ ucfirst($order['delivery_status'] ?? 'Pending') }}</span>
                                    @if($order['tracking_number'])
                                        <div class="text-xs text-gray-500 mt-1">#{{ $order['tracking_number'] }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $order['created_at']->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @if($order['type'] === 'pos')
                                        <a href="{{ route('admin.pos.order.show', $order['id']) }}" class="text-blue-600 hover:text-blue-800" title="View Order">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pos.order.receipt', $order['id']) }}" target="_blank" class="text-purple-600 hover:text-purple-800" title="Receipt">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="{{ route('admin.pos.order.print', $order['id']) }}" target="_blank" class="text-green-600 hover:text-green-800" title="Print Receipt">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.orders.show', $order['id']) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.invoice', $order['id']) }}" class="text-green-600 hover:text-green-800" title="Invoice" target="_blank">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.print', $order['id']) }}" class="text-purple-600 hover:text-purple-800" title="Print Receipt" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                                    <p>No orders found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
