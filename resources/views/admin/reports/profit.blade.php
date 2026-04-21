@extends('admin.layouts.master')

@section('title', 'Profit Report')
@section('page-title', 'Profit Report')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.reports.profit') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ $status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Generate Report
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Cost (COGS)</p>
            <p class="text-2xl font-bold text-red-600 mt-1">৳{{ number_format($summary['total_cost'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Gross Profit</p>
            <p class="text-2xl font-bold text-green-600 mt-1">৳{{ number_format($summary['total_profit'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Gross Margin</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['profit_margin'] }}%</p>
        </div>
    </div>

    <!-- Net Profit Section -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl shadow-sm p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">Net Profit Calculation</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
            <div class="text-center">
                <p class="text-sm text-gray-400">Revenue</p>
                <p class="text-xl font-bold">৳{{ number_format($netSummary['total_revenue'], 2) }}</p>
            </div>
            <div class="text-center text-red-400">
                <p class="text-lg">- COGS</p>
                <p class="text-xl font-bold">৳{{ number_format($netSummary['total_cost'], 2) }}</p>
            </div>
            <div class="text-center text-yellow-400">
                <p class="text-lg">- Expenses</p>
                <p class="text-xl font-bold">৳{{ number_format($netSummary['total_expenses'], 2) }}</p>
            </div>
            <div class="text-center text-2xl">=</div>
            <div class="text-center bg-white bg-opacity-10 rounded-lg p-4">
                <p class="text-sm text-gray-300">NET PROFIT</p>
                <p class="text-2xl font-bold {{ $netSummary['net_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    ৳{{ number_format($netSummary['net_profit'], 2) }}
                </p>
                <p class="text-sm text-gray-400">{{ $netSummary['net_margin'] }}% margin</p>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown -->
    @if(count($expenseBreakdown) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Expense Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">% of Expenses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($expenseBreakdown as $expense)
                        <tr>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" style="background-color: {{ $expense['color'] }}20; color: {{ $expense['color'] }}">
                                    {{ $expense['category_name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-medium text-red-600">৳{{ number_format($expense['total_amount'], 2) }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width: {{ $netSummary['total_expenses'] > 0 ? ($expense['total_amount'] / $netSummary['total_expenses'] * 100) : 0 }}%; background-color: {{ $expense['color'] }}"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $netSummary['total_expenses'] > 0 ? round($expense['total_amount'] / $netSummary['total_expenses'] * 100, 1) : 0 }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-6 py-3">Total Expenses</td>
                        <td class="px-6 py-3 text-red-600">৳{{ number_format($netSummary['total_expenses'], 2) }}</td>
                        <td class="px-6 py-3">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Orders</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_orders']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Items Sold</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_items_sold']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Avg Profit per Order</p>
            <p class="text-xl font-bold text-gray-800 mt-1">৳{{ number_format($summary['total_orders'] > 0 ? $summary['total_profit'] / $summary['total_orders'] : 0, 2) }}</p>
        </div>
    </div>

    <!-- Profit Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Profit</h3>
        <canvas id="profitChart" height="100"></canvas>
    </div>

    <!-- Product Profit Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Profit by Product</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $product['product_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $product['sku_prefix'] }}</div>
                            </td>
                            <td class="px-6 py-3">{{ $product['quantity_sold'] }}</td>
                            <td class="px-6 py-3">৳{{ number_format($product['revenue'], 2) }}</td>
                            <td class="px-6 py-3 text-red-600">৳{{ number_format($product['cost'], 2) }}</td>
                            <td class="px-6 py-3 font-medium {{ $product['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ৳{{ number_format($product['profit'], 2) }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $product['profit_margin'] >= 20 ? 'bg-green-100 text-green-800' : ($product['profit_margin'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $product['profit_margin'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">No profit data found for the selected period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products Missing Cost Price -->
    @if(count($missingCostPrice) > 0)
    <div class="bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-yellow-200">
            <h3 class="text-lg font-semibold text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Products Missing Cost Price ({{ count($missingCostPrice) }})
            </h3>
            <p class="text-sm text-yellow-700 mt-1">These products have no cost price set. Profit calculations assume cost = 0 for these items.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-yellow-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-yellow-800 uppercase">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-yellow-800 uppercase">SKU Prefix</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-yellow-800 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-yellow-200">
                    @foreach($missingCostPrice as $product)
                        <tr>
                            <td class="px-6 py-3">{{ $product->name }}</td>
                            <td class="px-6 py-3">{{ $product->sku_prefix }}</td>
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">Edit Product</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(array_column($daily, 'date')),
            datasets: [
                {
                    label: 'Revenue (৳)',
                    data: @json(array_column($daily, 'revenue')),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Cost (৳)',
                    data: @json(array_column($daily, 'cost')),
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                },
                {
                    label: 'Profit (৳)',
                    data: @json(array_column($daily, 'profit')),
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
