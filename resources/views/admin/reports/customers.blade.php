@extends('admin.layouts.master')

@section('title', 'Customers Report')
@section('page-title', 'Customers Report')

@section('content')
<div class="space-y-6">
    <!-- Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">New Customers (Last 30 Days)</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($newCustomers) }}</p>
            </div>
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-plus text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Top Customers -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Top Customers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topCustomers as $index => $customer)
                        <tr>
                            <td class="px-6 py-3">{{ $index + 1 }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $customer->email }}</td>
                            <td class="px-6 py-3">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{{ $customer->orders_count }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium">${{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No customer data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
