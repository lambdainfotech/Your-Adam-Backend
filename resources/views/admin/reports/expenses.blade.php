@extends('admin.layouts.master')

@section('title', 'Expense Report')
@section('page-title', 'Expense Report')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.reports.expenses') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Generate Report
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600 mt-1">৳{{ number_format($summary['total_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Top Category</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $topCategory['name'] ?? 'N/A' }}</p>
            <p class="text-sm text-gray-500">৳{{ number_format($topCategory['amount'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Avg Daily Expense</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ number_format($summary['average_daily'], 2) }}</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category Breakdown -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Expenses by Category</h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>

        <!-- Daily Trend -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Expense Trend</h3>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>

    <!-- Top Expenses -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Top Expenses</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topExpenses as $expense)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $expense['title'] }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" style="background-color: {{ $expense['category_color'] }}20; color: {{ $expense['category_color'] }}">
                                    {{ $expense['category_name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm">{{ $expense['date'] }}</td>
                            <td class="px-6 py-3 font-medium text-red-600">৳{{ number_format($expense['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No expenses found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Category Pie Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_column($byCategory, 'category_name')),
            datasets: [{
                data: @json(array_column($byCategory, 'total_amount')),
                backgroundColor: @json(array_column($byCategory, 'color')),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Daily Trend Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: @json(array_column($daily, 'date')),
            datasets: [{
                label: 'Daily Expenses (৳)',
                data: @json(array_column($daily, 'total_amount')),
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
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
