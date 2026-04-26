<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitReportService
{
    /**
     * Get profit summary for a date range (includes both regular & POS orders)
     */
    public function getProfitSummary(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';

        // Regular orders
        $regular = $this->baseOrderQuery($startDate, $endDate, $status)
            ->select(
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as total_cost'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(order_items.quantity) as total_items_sold')
            )->first();

        // POS orders
        $pos = $this->basePosOrderQuery($startDate, $endDate, $status)
            ->select(
                DB::raw('SUM(pos_order_items.total_price) as total_revenue'),
                DB::raw('SUM(pos_order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as total_cost'),
                DB::raw('COUNT(DISTINCT pos_orders.id) as total_orders'),
                DB::raw('SUM(pos_order_items.quantity) as total_items_sold')
            )->first();

        $totalRevenue = (float) ($regular->total_revenue ?? 0) + (float) ($pos->total_revenue ?? 0);
        $totalCost = (float) ($regular->total_cost ?? 0) + (float) ($pos->total_cost ?? 0);
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
            'total_orders' => (int) ($regular->total_orders ?? 0) + (int) ($pos->total_orders ?? 0),
            'total_items_sold' => (int) ($regular->total_items_sold ?? 0) + (int) ($pos->total_items_sold ?? 0),
        ];
    }

    /**
     * Get profit breakdown by product (includes both regular & POS orders)
     */
    public function getProfitByProduct(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';
        $limit = $filters['limit'] ?? 50;

        // Regular orders
        $regularProducts = $this->baseOrderQuery($startDate, $endDate, $status)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku_prefix',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost')
            )
            ->groupBy('products.id', 'products.name', 'products.sku_prefix')
            ->get()
            ->keyBy('product_id');

        // POS orders
        $posProducts = $this->basePosOrderQuery($startDate, $endDate, $status)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku_prefix',
                DB::raw('SUM(pos_order_items.quantity) as quantity_sold'),
                DB::raw('SUM(pos_order_items.total_price) as revenue'),
                DB::raw('SUM(pos_order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost')
            )
            ->groupBy('products.id', 'products.name', 'products.sku_prefix')
            ->get()
            ->keyBy('product_id');

        // Merge and aggregate
        $merged = collect();
        $allIds = $regularProducts->keys()->merge($posProducts->keys())->unique();

        foreach ($allIds as $id) {
            $reg = $regularProducts->get($id);
            $pos = $posProducts->get($id);

            $qty = (int) ($reg?->quantity_sold ?? 0) + (int) ($pos?->quantity_sold ?? 0);
            $revenue = (float) ($reg?->revenue ?? 0) + (float) ($pos?->revenue ?? 0);
            $cost = (float) ($reg?->cost ?? 0) + (float) ($pos?->cost ?? 0);
            $profit = $revenue - $cost;

            $merged->put($id, [
                'product_id' => $id,
                'product_name' => $reg?->product_name ?? $pos?->product_name,
                'sku_prefix' => $reg?->sku_prefix ?? $pos?->sku_prefix,
                'quantity_sold' => $qty,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            ]);
        }

        return $merged
            ->sortByDesc('profit')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Get daily profit breakdown (includes both regular & POS orders)
     */
    public function getDailyProfit(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';

        // Regular orders
        $regularDaily = $this->baseOrderQuery($startDate, $endDate, $status)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('COUNT(DISTINCT orders.id) as orders'),
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // POS orders
        $posDaily = $this->basePosOrderQuery($startDate, $endDate, $status)
            ->select(
                DB::raw('DATE(pos_orders.created_at) as date'),
                DB::raw('COUNT(DISTINCT pos_orders.id) as orders'),
                DB::raw('SUM(pos_order_items.total_price) as revenue'),
                DB::raw('SUM(pos_order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Merge and aggregate by date
        $merged = collect();
        $allDates = $regularDaily->keys()->merge($posDaily->keys())->unique()->sort();

        foreach ($allDates as $date) {
            $reg = $regularDaily->get($date);
            $pos = $posDaily->get($date);

            $orders = (int) ($reg?->orders ?? 0) + (int) ($pos?->orders ?? 0);
            $revenue = (float) ($reg?->revenue ?? 0) + (float) ($pos?->revenue ?? 0);
            $cost = (float) ($reg?->cost ?? 0) + (float) ($pos?->cost ?? 0);
            $profit = $revenue - $cost;

            $merged->put($date, [
                'date' => $date,
                'orders' => $orders,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            ]);
        }

        return $merged->values()->toArray();
    }

    /**
     * Get net profit summary (revenue - COGS - expenses)
     */
    public function getNetProfitSummary(string $startDate, string $endDate, array $filters = []): array
    {
        $grossSummary = $this->getProfitSummary($startDate, $endDate, $filters);
        $totalExpenses = (float) Expense::byDateRange($startDate, $endDate)->sum('amount');
        $netProfit = $grossSummary['total_profit'] - $totalExpenses;

        return [
            'total_revenue' => $grossSummary['total_revenue'],
            'total_cost' => $grossSummary['total_cost'],
            'gross_profit' => $grossSummary['total_profit'],
            'gross_margin' => $grossSummary['profit_margin'],
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'net_margin' => $grossSummary['total_revenue'] > 0 ? round(($netProfit / $grossSummary['total_revenue']) * 100, 2) : 0,
            'total_orders' => $grossSummary['total_orders'],
            'total_items_sold' => $grossSummary['total_items_sold'],
        ];
    }

    /**
     * Get expense breakdown by category for profit report
     */
    public function getExpenseBreakdown(string $startDate, string $endDate): array
    {
        return DB::table('expenses')
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->select(
                'expense_categories.name as category_name',
                'expense_categories.color',
                DB::raw('SUM(expenses.amount) as total_amount')
            )
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.color')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                return [
                    'category_name' => $item->category_name,
                    'color' => $item->color,
                    'total_amount' => (float) $item->total_amount,
                ];
            })
            ->toArray();
    }

    /**
     * Get products with missing cost price
     */
    public function getProductsWithoutCostPrice(): array
    {
        return DB::table('products')
            ->select('products.id', 'products.name', 'products.sku_prefix')
            ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
            ->where(function ($query) {
                $query->whereNull('products.cost_price')
                    ->orWhere('products.cost_price', 0);
            })
            ->where(function ($query) {
                $query->whereNull('variants.cost_price')
                    ->orWhere('variants.cost_price', 0);
            })
            ->groupBy('products.id', 'products.name', 'products.sku_prefix')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Base query for regular order profit calculations
     */
    protected function baseOrderQuery(string $startDate, string $endDate, string $status)
    {
        $query = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('variants', 'order_items.variant_id', '=', 'variants.id')
            ->join('products', 'variants.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('orders.status', $status);
        }

        return $query;
    }

    /**
     * Base query for POS order profit calculations
     */
    protected function basePosOrderQuery(string $startDate, string $endDate, string $status)
    {
        $query = DB::table('pos_orders')
            ->join('pos_order_items', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->leftJoin('variants', 'pos_order_items.product_variant_id', '=', 'variants.id')
            ->join('products', 'pos_order_items.product_id', '=', 'products.id')
            ->whereBetween('pos_orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('pos_orders.status', $status);
        }

        return $query;
    }
}
