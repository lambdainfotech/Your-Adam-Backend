<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitReportService
{
    /**
     * Get profit summary for a date range
     */
    public function getProfitSummary(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';

        $query = $this->baseOrderQuery($startDate, $endDate, $status);

        $summary = $query->select(
            DB::raw('SUM(order_items.total_price) as total_revenue'),
            DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as total_cost'),
            DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
            DB::raw('SUM(order_items.quantity) as total_items_sold')
        )->first();

        $totalRevenue = (float) ($summary->total_revenue ?? 0);
        $totalCost = (float) ($summary->total_cost ?? 0);
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
            'total_orders' => (int) ($summary->total_orders ?? 0),
            'total_items_sold' => (int) ($summary->total_items_sold ?? 0),
        ];
    }

    /**
     * Get profit breakdown by product
     */
    public function getProfitByProduct(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';
        $limit = $filters['limit'] ?? 50;

        $products = $this->baseOrderQuery($startDate, $endDate, $status)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku_prefix',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost'),
                DB::raw('SUM(order_items.total_price) - SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as profit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku_prefix')
            ->orderByDesc('profit')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $revenue = (float) $item->revenue;
                $cost = (float) $item->cost;
                $profit = (float) $item->profit;

                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku_prefix' => $item->sku_prefix,
                    'quantity_sold' => (int) $item->quantity_sold,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                ];
            })
            ->toArray();

        return $products;
    }

    /**
     * Get daily profit breakdown
     */
    public function getDailyProfit(string $startDate, string $endDate, array $filters = []): array
    {
        $status = $filters['status'] ?? 'completed';

        $daily = $this->baseOrderQuery($startDate, $endDate, $status)
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('COUNT(DISTINCT orders.id) as orders'),
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as cost'),
                DB::raw('SUM(order_items.total_price) - SUM(order_items.quantity * COALESCE(variants.cost_price, products.cost_price, 0)) as profit')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $revenue = (float) $item->revenue;
                $profit = (float) $item->profit;

                return [
                    'date' => $item->date,
                    'orders' => (int) $item->orders,
                    'revenue' => $revenue,
                    'cost' => (float) $item->cost,
                    'profit' => $profit,
                    'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
                ];
            })
            ->toArray();

        return $daily;
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
     * Base query for profit calculations
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
}
