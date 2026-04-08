@extends('admin.layouts.master')

@section('title', 'Inventory Valuation')
@section('page-title', 'Inventory Valuation')

@section('content')
<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Inventory Valuation</h2>
            <p class="text-gray-500">Total value of your inventory</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-warehouse mr-2"></i>Back to Inventory
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Inventory Value</p>
                    <p class="text-2xl font-bold text-gray-800">৳{{ number_format($valuation['total'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-box text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Simple Products Value</p>
                    <p class="text-2xl font-bold text-gray-800">৳{{ number_format($valuation['simple_products'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-cubes text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Variants Value</p>
                    <p class="text-2xl font-bold text-gray-800">৳{{ number_format($valuation['variants'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Valuation by Category</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">% of Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $totalValue = $valuation['total']; @endphp
                    @forelse($categoryValuation as $categoryName => $value)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $categoryName ?: 'Uncategorized' }}</td>
                        <td class="px-6 py-4 text-right font-semibold">৳{{ number_format($value, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $totalValue > 0 ? ($value / $totalValue * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $totalValue > 0 ? number_format($value / $totalValue * 100, 1) : 0 }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-chart-pie text-4xl mb-3"></i>
                            <p>No data available</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Valuation Method Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">
            <i class="fas fa-info-circle mr-2"></i>Valuation Method
        </h4>
        <p class="text-blue-700 text-sm">
            Inventory value is calculated using the <strong>cost price</strong> of each product. 
            If cost price is not set, it estimates based on 60% of the base price.
        </p>
    </div>
</div>
@endsection
