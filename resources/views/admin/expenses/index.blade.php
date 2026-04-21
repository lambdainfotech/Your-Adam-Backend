@extends('admin.layouts.master')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Expenses</h2>
            <p class="text-gray-500 mt-1">Track and manage business expenses</p>
        </div>
        <a href="{{ route('admin.expenses.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>Add Expense
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <a href="{{ route('admin.expenses.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Reset
            </a>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600 mt-1">৳{{ number_format($summary['total_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Expense Count</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_count']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Average per Expense</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">৳{{ number_format($summary['average_daily'], 2) }}</p>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="px-6 py-3 text-sm">{{ $expense->date->format('M d, Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-900">{{ $expense->title }}</div>
                                @if($expense->reference_no)
                                    <div class="text-xs text-gray-500">Ref: {{ $expense->reference_no }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" style="background-color: {{ $expense->category?->color }}20; color: {{ $expense->category?->color }}">
                                    <i class="fas {{ $expense->category?->icon }} mr-1"></i>
                                    {{ $expense->category?->name }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-medium text-red-600">৳{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ ucfirst($expense->payment_method ?? 'N/A') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">No expenses found for the selected period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
