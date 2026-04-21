<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseReportService
{
    /**
     * Get expense summary for a date range
     */
    public function getExpenseSummary(string $startDate, string $endDate, ?int $categoryId = null): array
    {
        $query = Expense::byDateRange($startDate, $endDate);

        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        $totalAmount = (float) $query->sum('amount');
        $totalCount = $query->count();

        return [
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'average_daily' => $totalCount > 0 ? round($totalAmount / $totalCount, 2) : 0,
        ];
    }

    /**
     * Get expenses grouped by category
     */
    public function getExpensesByCategory(string $startDate, string $endDate): array
    {
        return DB::table('expenses')
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->select(
                'expense_categories.id as category_id',
                'expense_categories.name as category_name',
                'expense_categories.icon',
                'expense_categories.color',
                DB::raw('SUM(expenses.amount) as total_amount'),
                DB::raw('COUNT(expenses.id) as expense_count')
            )
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.icon', 'expense_categories.color')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category_name,
                    'icon' => $item->icon,
                    'color' => $item->color,
                    'total_amount' => (float) $item->total_amount,
                    'expense_count' => (int) $item->expense_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get daily expense breakdown
     */
    public function getDailyExpenses(string $startDate, string $endDate): array
    {
        return Expense::byDateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(date) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as expense_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_amount' => (float) $item->total_amount,
                    'expense_count' => (int) $item->expense_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly expense breakdown for a year
     */
    public function getMonthlyExpenses(int $year): array
    {
        return Expense::whereYear('date', $year)
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as expense_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => (int) $item->month,
                    'month_name' => Carbon::create()->month($item->month)->format('F'),
                    'total_amount' => (float) $item->total_amount,
                    'expense_count' => (int) $item->expense_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get top expenses
     */
    public function getTopExpenses(string $startDate, string $endDate, int $limit = 10): array
    {
        return Expense::with('category')
            ->byDateRange($startDate, $endDate)
            ->orderByDesc('amount')
            ->limit($limit)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'title' => $expense->title,
                    'amount' => (float) $expense->amount,
                    'date' => $expense->date->toDateString(),
                    'category_name' => $expense->category?->name,
                    'category_color' => $expense->category?->color,
                    'payment_method' => $expense->payment_method,
                ];
            })
            ->toArray();
    }

    /**
     * Get total expenses for net profit calculation
     */
    public function getTotalExpenses(string $startDate, string $endDate): float
    {
        return (float) Expense::byDateRange($startDate, $endDate)->sum('amount');
    }
}
