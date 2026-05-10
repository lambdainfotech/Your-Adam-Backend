@extends('admin.layouts.master')

@section('title', 'Guest Details')
@section('page-title', 'Guest Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.guests.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Guest Details</h2>
            <p class="text-gray-500 mt-1">#{{ $guest->id }} — {{ $guest->name }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Guest Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-2xl font-bold">
                        {{ substr($guest->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $guest->name }}</h3>
                        <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                            <i class="fas fa-user-clock mr-1"></i>Guest Customer
                        </span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-envelope text-gray-400 mt-1 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-gray-800">{{ $guest->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone text-gray-400 mt-1 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="text-gray-800">{{ $guest->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-globe text-gray-400 mt-1 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-500">IP Address</p>
                            <p class="text-gray-800">{{ $guest->ip_address ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-calendar text-gray-400 mt-1 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-500">Registered On</p>
                            <p class="text-gray-800">{{ $guest->created_at->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold text-gray-800 mb-4">Order Statistics</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $guest->orders->count() }}</p>
                        <p class="text-sm text-gray-600">Total Orders</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">
                            ৳{{ number_format($guest->orders->sum('total_amount'), 2) }}
                        </p>
                        <p class="text-sm text-gray-600">Total Spent</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Order History</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($guest->orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-blue-600">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        ৳{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $order->status_badge_class }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                        @if($order->payment_method)
                                            <div class="text-xs text-gray-500 mt-1">{{ strtoupper($order->payment_method) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-sm">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
                                        <p>No orders found for this guest.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
