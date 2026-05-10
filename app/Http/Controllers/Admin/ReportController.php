<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ProfitReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');
        
        $sales = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $totalRevenue = $sales->sum('revenue');
        $totalOrders = $sales->sum('orders');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        return view('admin.reports.sales', compact('sales', 'totalRevenue', 'totalOrders', 'averageOrderValue', 'startDate', 'endDate'));
    }

    public function products(Request $request)
    {
        $topProducts = OrderItem::topSelling(50)->get();
        
        $lowStockProducts = Product::whereHas('variants', function($query) {
            $query->where('stock_quantity', '<=', 10);
        })->with(['variants' => function($query) {
            $query->where('stock_quantity', '<=', 10);
        }])->get();
        
        return view('admin.reports.products', compact('topProducts', 'lowStockProducts'));
    }

    public function customers(Request $request)
    {
        $topCustomers = User::withCount(['orders' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['orders' => function($query) {
                $query->where('status', 'completed');
            }], 'total_amount')
            ->orderBy('orders_sum_total_amount', 'desc')
            ->limit(50)
            ->get();
        
        $newCustomers = User::where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        return view('admin.reports.customers', compact('topCustomers', 'newCustomers'));
    }

    public function inventory(Request $request)
    {
        $products = Product::with(['variants', 'category'])
            ->whereHas('variants', function($query) {
                $query->where('stock_quantity', '<=', 20);
            })
            ->get();
        
        $totalProducts = Product::count();
        $outOfStock = Product::whereHas('variants', function($query) {
            $query->where('stock_quantity', 0);
        })->count();
        
        $lowStock = Product::whereHas('variants', function($query) {
            $query->where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0);
        })->count();
        
        return view('admin.reports.inventory', compact('products', 'totalProducts', 'outOfStock', 'lowStock'));
    }

    public function expenses(Request $request, \App\Services\ExpenseReportService $service)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        $summary = $service->getExpenseSummary($startDate, $endDate);
        $byCategory = $service->getExpensesByCategory($startDate, $endDate);
        $daily = $service->getDailyExpenses($startDate, $endDate);
        $topExpenses = $service->getTopExpenses($startDate, $endDate, 10);

        $topCategory = collect($byCategory)->first();

        return view('admin.reports.expenses', compact(
            'summary',
            'byCategory',
            'daily',
            'topExpenses',
            'topCategory',
            'startDate',
            'endDate'
        ));
    }

    public function profit(Request $request, ProfitReportService $service)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');
        $status = $validated['status'] ?? 'completed';

        $filters = ['status' => $status];

        $summary = $service->getProfitSummary($startDate, $endDate, $filters);
        $netSummary = $service->getNetProfitSummary($startDate, $endDate, $filters);
        $expenseBreakdown = $service->getExpenseBreakdown($startDate, $endDate);
        $products = $service->getProfitByProduct($startDate, $endDate, $filters);
        $daily = $service->getDailyProfit($startDate, $endDate, $filters);
        $missingCostPrice = $service->getProductsWithoutCostPrice();

        return view('admin.reports.profit', compact(
            'summary',
            'netSummary',
            'expenseBreakdown',
            'products',
            'daily',
            'missingCostPrice',
            'startDate',
            'endDate',
            'status'
        ));
    }
}
