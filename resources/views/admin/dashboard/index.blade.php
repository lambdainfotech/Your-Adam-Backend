@extends('admin.layouts.master')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold">{{ $totalOrders ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> {{ $newOrdersToday ?? 0 }} new today
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Revenue</p>
                <p class="text-2xl font-bold">৳{{ number_format($totalRevenue ?? 0, 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> ৳{{ number_format($revenueToday ?? 0, 2) }} today
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Products</p>
                <p class="text-2xl font-bold">{{ $totalProducts ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-red-600">
            <i class="fas fa-exclamation-circle"></i> {{ $lowStockProducts ?? 0 }} low stock
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Customers</p>
                <p class="text-2xl font-bold">{{ $totalCustomers ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-yellow-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> {{ $newCustomersToday ?? 0 }} new today
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-semibold">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-blue-600 text-sm">View All</a>
        </div>
        <div class="p-6">
            @if(isset($recentOrders) && $recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium">{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-500">{{ $order->user?->name ?? 'Guest' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">৳{{ number_format($order->total_amount, 2) }}</p>
                            <span class="px-2 py-1 text-xs rounded-full {{ $order->status_badge_class }}">{{ $order->status }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No recent orders</p>
            @endif
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-semibold">Low Stock Alert</h3>
            <a href="{{ route('admin.inventory.index') }}" class="text-blue-600 text-sm">View Inventory</a>
        </div>
        <div class="p-6">
            @if(isset($lowStockItems) && $lowStockItems->count() > 0)
                <div class="space-y-4">
                    @foreach($lowStockItems as $item)
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-medium">{{ $item->product?->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">SKU: {{ $item->sku ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-red-600">{{ $item->stock_quantity }} left</p>
                            <a href="{{ route('admin.stock-in.bulk') }}" class="text-sm text-blue-600">Restock</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-green-600 text-center py-8"><i class="fas fa-check-circle mr-2"></i>All products are well stocked</p>
            @endif
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.products.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100">
            <i class="fas fa-plus-circle text-2xl text-blue-600 mb-2"></i>
            <span class="text-sm font-medium">Add Product</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100">
            <i class="fas fa-clipboard-list text-2xl text-green-600 mb-2"></i>
            <span class="text-sm font-medium">View Orders</span>
        </a>
        <a href="{{ route('admin.coupons.create') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100">
            <i class="fas fa-ticket-alt text-2xl text-purple-600 mb-2"></i>
            <span class="text-sm font-medium">Create Coupon</span>
        </a>
        <a href="{{ route('admin.stock-in.bulk') }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100">
            <i class="fas fa-boxes text-2xl text-yellow-600 mb-2"></i>
            <span class="text-sm font-medium">Stock In</span>
        </a>
    </div>
</div>
@endsection
