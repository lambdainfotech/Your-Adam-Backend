<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        ];
        
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $lowStockProducts = Product::whereHas('variants', function($query) {
            $query->where('stock_quantity', '<=', 10);
        })->with(['variants' => function($query) {
            $query->where('stock_quantity', '<=', 10);
        }])->limit(5)->get();
        
        $salesChart = $this->getSalesChartData();
        
        $topProducts = DB::table('order_items')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('variants', 'order_items.variant_id', '=', 'variants.id')
            ->join('products', 'variants.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // Prepare data for view
        $totalOrders = $stats['total_orders'];
        $totalRevenue = $stats['total_revenue'];
        $totalProducts = $stats['total_products'];
        $totalCustomers = $stats['total_users'];
        $newOrdersToday = Order::whereDate('created_at', today())->count();
        $revenueToday = Order::whereDate('created_at', today())->sum('total_amount');
        $newCustomersToday = User::whereDate('created_at', today())->count();
        $lowStockProducts = Product::whereHas('variants', fn($q) => $q->where('stock_quantity', '<=', 5))->count();
        
        // Flatten low stock variants for display
        $lowStockItems = \App\Models\Variant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->where('is_active', true)
            ->limit(5)
            ->get();
        
        return view('admin.dashboard.index', compact(
            'stats', 
            'recentOrders', 
            'salesChart',
            'topProducts',
            'totalOrders',
            'totalRevenue',
            'totalProducts',
            'totalCustomers',
            'newOrdersToday',
            'revenueToday',
            'newCustomersToday',
            'lowStockProducts',
            'lowStockItems'
        ));
    }
    
    private function getSalesChartData()
    {
        $days = [];
        $sales = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            
            $dailySales = Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $sales[] = round($dailySales, 2);
        }
        
        return [
            'labels' => $days,
            'data' => $sales
        ];
    }
}
