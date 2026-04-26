<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $weekAgo = now()->subDays(6)->startOfDay();
        $monthAgo = now()->subDays(29)->startOfDay();

        // Regular orders
        $regularOrders = Order::query();
        $regularToday = Order::whereDate('created_at', $today);
        $regularWeek = Order::where('created_at', '>=', $weekAgo);

        // POS orders
        $posOrders = PosOrder::query();
        $posToday = PosOrder::whereDate('created_at', $today);
        $posWeek = PosOrder::where('created_at', '>=', $weekAgo);

        // Combined stats
        $totalOrders = Order::count() + PosOrder::count();
        $totalRevenue = (float) Order::sum('total_amount') + (float) PosOrder::sum('total_amount');
        $totalProducts = Product::count();
        $totalCustomers = User::count();

        // Today's stats
        $newOrdersToday = Order::whereDate('created_at', $today)->count() + PosOrder::whereDate('created_at', $today)->count();
        $revenueToday = (float) Order::whereDate('created_at', $today)->sum('total_amount') + (float) PosOrder::whereDate('created_at', $today)->sum('total_amount');
        $newCustomersToday = User::whereDate('created_at', $today)->count();

        // Order statuses
        $stats = [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_users' => $totalCustomers,
            'total_revenue' => $totalRevenue,
            'pending_orders' => Order::where('status', 'pending')->count() + PosOrder::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count() + PosOrder::where('delivery_status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count() + PosOrder::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count() + PosOrder::where('delivery_status', 'cancelled')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count() + PosOrder::where('delivery_status', 'shipped')->count(),
        ];

        // Recent orders (both regular + POS)
        $recentRegularOrders = Order::with('user')
            ->select('id', 'order_number as number', 'total_amount', 'status', 'created_at', DB::raw("'online' as type"))
            ->orderBy('created_at', 'desc')
            ->limit(5);

        $recentPosOrders = PosOrder::with('user')
            ->select('id', 'order_number as number', 'total_amount', DB::raw("COALESCE(delivery_status, status) as status"), 'created_at', DB::raw("'pos' as type"))
            ->orderBy('created_at', 'desc')
            ->limit(5);

        $recentOrders = $recentRegularOrders->union($recentPosOrders)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Low stock
        $lowStockItems = \App\Models\Variant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->where('is_active', true)
            ->limit(5)
            ->get();

        $lowStockCount = \App\Models\Variant::where('stock_quantity', '<=', 5)->where('is_active', true)->count();

        // Sales chart (last 7 days, combined)
        $salesChart = $this->getSalesChartData();

        // Order status breakdown for chart
        $statusChart = [
            'labels' => ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'],
            'data' => [
                $stats['pending_orders'],
                $stats['processing_orders'],
                $stats['shipped_orders'],
                $stats['completed_orders'],
                $stats['cancelled_orders'],
            ],
        ];

        // Top products
        $topProducts = OrderItem::topSelling(5)->get();

        // Weekly comparison
        $revenueThisWeek = (float) Order::where('created_at', '>=', $weekAgo)->sum('total_amount')
            + (float) PosOrder::where('created_at', '>=', $weekAgo)->sum('total_amount');
        $revenueLastWeek = (float) Order::whereBetween('created_at', [now()->subDays(13)->startOfDay(), $weekAgo->copy()->subDay()->endOfDay()])->sum('total_amount')
            + (float) PosOrder::whereBetween('created_at', [now()->subDays(13)->startOfDay(), $weekAgo->copy()->subDay()->endOfDay()])->sum('total_amount');

        $ordersThisWeek = Order::where('created_at', '>=', $weekAgo)->count() + PosOrder::where('created_at', '>=', $weekAgo)->count();
        $ordersLastWeek = Order::whereBetween('created_at', [now()->subDays(13)->startOfDay(), $weekAgo->copy()->subDay()->endOfDay()])->count()
            + PosOrder::whereBetween('created_at', [now()->subDays(13)->startOfDay(), $weekAgo->copy()->subDay()->endOfDay()])->count();

        $revenueGrowth = $revenueLastWeek > 0 ? round((($revenueThisWeek - $revenueLastWeek) / $revenueLastWeek) * 100, 1) : 0;
        $ordersGrowth = $ordersLastWeek > 0 ? round((($ordersThisWeek - $ordersLastWeek) / $ordersLastWeek) * 100, 1) : 0;

        // Today's expenses
        $expensesToday = (float) Expense::whereDate('date', $today)->sum('amount');

        return view('admin.dashboard.index', compact(
            'stats',
            'recentOrders',
            'salesChart',
            'statusChart',
            'topProducts',
            'totalOrders',
            'totalRevenue',
            'totalProducts',
            'totalCustomers',
            'newOrdersToday',
            'revenueToday',
            'newCustomersToday',
            'lowStockCount',
            'lowStockItems',
            'revenueGrowth',
            'ordersGrowth',
            'expensesToday',
            'revenueThisWeek',
            'ordersThisWeek'
        ));
    }

    private function getSalesChartData()
    {
        $days = [];
        $sales = [];
        $posSales = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');

            $dailyRegular = Order::whereDate('created_at', $date)
                ->sum('total_amount');

            $dailyPos = PosOrder::whereDate('created_at', $date)
                ->sum('total_amount');

            $sales[] = round($dailyRegular, 2);
            $posSales[] = round($dailyPos, 2);
        }

        return [
            'labels' => $days,
            'data' => $sales,
            'posData' => $posSales,
        ];
    }
}
