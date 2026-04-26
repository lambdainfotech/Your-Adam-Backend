@extends('admin.layouts.master')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6" x-data="dashboard()" x-init="initCharts()">

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 p-5 text-white shadow-lg">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-xs font-medium uppercase tracking-wider">Total Revenue</p>
                        <p class="text-2xl font-bold mt-1">৳{{ number_format($totalRevenue, 0) }}</p>
                    </div>
                    <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-medium">
                        <i class="fas fa-arrow-up text-[10px]"></i> ৳{{ number_format($revenueToday, 0) }}
                    </span>
                    <span class="text-indigo-200 text-xs">today</span>
                </div>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-white/10 rounded-full"></div>
        </div>

        <!-- Orders -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider">Total Orders</p>
                        <p class="text-2xl font-bold mt-1">{{ number_format($totalOrders) }}</p>
                    </div>
                    <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-medium">
                        <i class="fas fa-arrow-up text-[10px]"></i> {{ $newOrdersToday }}
                    </span>
                    <span class="text-emerald-200 text-xs">new today</span>
                </div>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>

        <!-- Products -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 text-white shadow-lg">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-100 text-xs font-medium uppercase tracking-wider">Products</p>
                        <p class="text-2xl font-bold mt-1">{{ number_format($totalProducts) }}</p>
                    </div>
                    <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-box text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    @if($lowStockCount > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-500/80 rounded-full text-xs font-medium">
                            <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $lowStockCount }} low
                        </span>
                        <span class="text-amber-200 text-xs">stock alert</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-medium">
                            <i class="fas fa-check text-[10px]"></i> All good
                        </span>
                    @endif
                </div>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>

        <!-- Customers -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 p-5 text-white shadow-lg">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-rose-100 text-xs font-medium uppercase tracking-wider">Customers</p>
                        <p class="text-2xl font-bold mt-1">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-medium">
                        <i class="fas fa-arrow-up text-[10px]"></i> {{ $newCustomersToday }}
                    </span>
                    <span class="text-rose-200 text-xs">new today</span>
                </div>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Sales Overview</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Last 7 days performance</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Online
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> POS
                    </span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Order Status Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Order Status</h3>
                <p class="text-xs text-slate-400 mt-0.5">Current order breakdown</p>
            </div>
            <div class="h-48 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                @foreach(['Pending' => 'bg-yellow-400', 'Processing' => 'bg-blue-400', 'Shipped' => 'bg-indigo-400', 'Completed' => 'bg-emerald-400', 'Cancelled' => 'bg-red-400'] as $label => $color)
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                        <span class="text-slate-500">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Middle Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Weekly Performance -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 text-sm mb-4">This Week vs Last Week</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-wallet text-indigo-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Revenue</p>
                            <p class="font-bold text-slate-800">৳{{ number_format($revenueThisWeek, 0) }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold {{ $revenueGrowth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($revenueGrowth) }}%
                    </span>
                </div>
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-emerald-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Orders</p>
                            <p class="font-bold text-slate-800">{{ number_format($ordersThisWeek) }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold {{ $ordersGrowth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        <i class="fas fa-arrow-{{ $ordersGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($ordersGrowth) }}%
                    </span>
                </div>
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-receipt text-rose-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Today's Expenses</p>
                            <p class="font-bold text-slate-800">৳{{ number_format($expensesToday, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Top Products</h3>
                <a href="{{ route('admin.reports.products') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All</a>
            </div>
            @if(isset($topProducts) && $topProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $index => $product)
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg {{ $index < 3 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xs font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $product->product_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-400">{{ $product->total_sold ?? 0 }} sold</p>
                            </div>
                            <p class="text-sm font-bold text-slate-800">৳{{ number_format($product->total_revenue ?? 0, 0) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-sm text-center py-6">No sales data yet</p>
            @endif
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Low Stock Alert</h3>
                <a href="{{ route('admin.stock-in.bulk') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Restock</a>
            </div>
            @if(isset($lowStockItems) && $lowStockItems->count() > 0)
                <div class="space-y-3">
                    @foreach($lowStockItems as $item)
                        <div class="flex items-center gap-3 p-2.5 bg-red-50 rounded-xl border border-red-100">
                            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm">
                                <i class="fas fa-box text-red-400 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $item->product?->name ?? 'Unknown' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $item->sku ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-red-600">{{ $item->stock_quantity }}</p>
                                <p class="text-[10px] text-red-400">left</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-2">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">All products well stocked</p>
                    <p class="text-xs text-slate-400 mt-0.5">No restocking needed</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Recent Orders</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest online & POS orders</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg font-medium transition-colors">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Order</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Customer</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3">
                                <span class="font-semibold text-slate-700">#{{ $order->number ?? $order->order_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if(($order->type ?? 'online') === 'pos')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-cash-register text-[10px]"></i> POS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                        <i class="fas fa-globe text-[10px]"></i> Online
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $order->user?->name ?? 'Guest' }}
                            </td>
                            <td class="px-5 py-3 text-slate-500">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">
                                ৳{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    $status = $order->status ?? 'pending';
                                    $badgeClass = match($status) {
                                        'completed', 'delivered' => 'bg-emerald-100 text-emerald-700',
                                        'processing', 'ready', 'shipped' => 'bg-blue-100 text-blue-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mb-2">
                                        <i class="fas fa-inbox text-slate-300"></i>
                                    </div>
                                    <p class="text-sm">No recent orders</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @php
            $actions = [
                ['route' => route('admin.products.create'), 'icon' => 'fa-plus-circle', 'label' => 'Add Product', 'color' => 'bg-blue-50 text-blue-600 hover:bg-blue-100'],
                ['route' => route('admin.orders.index'), 'icon' => 'fa-clipboard-list', 'label' => 'Orders', 'color' => 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'],
                ['route' => route('admin.pos.index'), 'icon' => 'fa-cash-register', 'label' => 'POS', 'color' => 'bg-purple-50 text-purple-600 hover:bg-purple-100'],
                ['route' => route('admin.coupons.create'), 'icon' => 'fa-ticket-alt', 'label' => 'Coupon', 'color' => 'bg-amber-50 text-amber-600 hover:bg-amber-100'],
                ['route' => route('admin.stock-in.bulk'), 'icon' => 'fa-boxes', 'label' => 'Stock In', 'color' => 'bg-rose-50 text-rose-600 hover:bg-rose-100'],
                ['route' => route('admin.expenses.create'), 'icon' => 'fa-receipt', 'label' => 'Expense', 'color' => 'bg-cyan-50 text-cyan-600 hover:bg-cyan-100'],
            ];
        @endphp
        @foreach($actions as $action)
            <a href="{{ $action['route'] }}" class="flex flex-col items-center gap-2 p-4 rounded-xl {{ $action['color'] }} transition-colors">
                <i class="fas {{ $action['icon'] }} text-xl"></i>
                <span class="text-xs font-semibold">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function dashboard() {
    return {
        initCharts() {
            const salesCtx = document.getElementById('salesChart');
            const statusCtx = document.getElementById('statusChart');

            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($salesChart['labels'] ?? []),
                        datasets: [
                            {
                                label: 'Online Sales',
                                data: @json($salesChart['data'] ?? []),
                                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                                borderColor: 'rgba(99, 102, 241, 1)',
                                borderWidth: 0,
                                borderRadius: 6,
                                barPercentage: 0.6,
                            },
                            {
                                label: 'POS Sales',
                                data: @json($salesChart['posData'] ?? []),
                                backgroundColor: 'rgba(52, 211, 153, 0.8)',
                                borderColor: 'rgba(52, 211, 153, 1)',
                                borderWidth: 0,
                                borderRadius: 6,
                                barPercentage: 0.6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: (ctx) => '৳' + Number(ctx.raw).toLocaleString()
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 }, color: '#94a3b8' }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 11 },
                                    color: '#94a3b8',
                                    callback: (v) => '৳' + (v >= 1000 ? (v/1000) + 'k' : v)
                                },
                                border: { display: false }
                            }
                        }
                    }
                });
            }

            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($statusChart['labels'] ?? []),
                        datasets: [{
                            data: @json($statusChart['data'] ?? []),
                            backgroundColor: [
                                '#facc15', // Pending - yellow
                                '#60a5fa', // Processing - blue
                                '#818cf8', // Shipped - indigo
                                '#34d399', // Completed - emerald
                                '#f87171', // Cancelled - red
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                cornerRadius: 8,
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>
@endpush
