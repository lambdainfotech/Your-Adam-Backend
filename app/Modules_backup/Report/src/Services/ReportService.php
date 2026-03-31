<?php

declare(strict_types=1);

namespace App\Modules\Report\Services;

use App\Modules\Catalog\Repositories\ProductRepository;
use App\Modules\Catalog\Repositories\VariantRepository;
use App\Modules\Report\Contracts\ReportServiceInterface;
use App\Modules\Report\DTOs\ReportFilterDTO;
use App\Modules\Report\Enums\ReportType;
use App\Modules\Sales\Repositories\CouponRepository;
use App\Modules\Sales\Repositories\OrderRepository;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportService implements ReportServiceInterface
{
    public function __construct(
        private OrderRepository $orderRepository,
        private VariantRepository $variantRepository,
        private UserRepository $userRepository,
        private CouponRepository $couponRepository,
        private ProductRepository $productRepository
    ) {}

    public function generateSalesReport(ReportFilterDTO $filters): array
    {
        $query = $this->orderRepository->query()
            ->whereBetween('created_at', [$filters->dateFrom . ' 00:00:00', $filters->dateTo . ' 23:59:59']);

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        $totalSales = $query->sum('total_amount');
        $totalOrders = $query->count();
        $totalDiscount = $query->sum('discount_amount');
        $totalShipping = $query->sum('shipping_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $groupBy = $filters->groupBy ?? 'day';
        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $details = $query->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period, SUM(total_amount) as sales, COUNT(*) as orders, SUM(discount_amount) as discounts")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'summary' => [
                'total_sales' => (float) $totalSales,
                'total_orders' => $totalOrders,
                'average_order_value' => round($averageOrderValue, 2),
                'total_discounts' => (float) $totalDiscount,
                'total_shipping' => (float) $totalShipping,
            ],
            'details' => $details,
        ];
    }

    public function generateInventoryReport(): array
    {
        $query = $this->variantRepository->query();
        
        $lowStock = (clone $query)->whereColumn('stock_quantity', '<=', 'low_stock_alert')->count();
        $outOfStock = (clone $query)->where('stock_quantity', 0)->count();
        $inStock = (clone $query)->where('stock_quantity', '>', 0)->count();
        $totalVariants = $query->count();
        
        $totalValue = (clone $query)->sum(DB::raw('stock_quantity * cost_price'));
        
        $lowStockItems = (clone $query)->whereColumn('stock_quantity', '<=', 'low_stock_alert')
            ->with('product:id,name,slug')
            ->limit(20)
            ->get(['id', 'product_id', 'sku', 'stock_quantity', 'low_stock_alert']);

        return [
            'summary' => [
                'total_variants' => $totalVariants,
                'in_stock' => $inStock,
                'low_stock_count' => $lowStock,
                'out_of_stock_count' => $outOfStock,
                'total_inventory_value' => round($totalValue, 2),
            ],
            'low_stock_items' => $lowStockItems,
        ];
    }

    public function generateCustomerReport(ReportFilterDTO $filters): array
    {
        $query = $this->userRepository->query()
            ->whereBetween('created_at', [$filters->dateFrom . ' 00:00:00', $filters->dateTo . ' 23:59:59']);

        $totalCustomers = $query->count();
        $newCustomers = (clone $query)->where('created_at', '>=', now()->subDays(30))->count();
        
        $topCustomers = $this->orderRepository->query()
            ->selectRaw('user_id, COUNT(*) as orders, SUM(total_amount) as total_spent')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->with('user:id,mobile')
            ->get();

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'new_customers_last_30_days' => $newCustomers,
            ],
            'top_customers' => $topCustomers,
        ];
    }

    public function generateCouponReport(ReportFilterDTO $filters): array
    {
        $query = $this->couponRepository->query();
        
        $totalCoupons = $query->count();
        $activeCoupons = (clone $query)->where('is_active', true)->count();
        $expiredCoupons = (clone $query)->where('expires_at', '<', now())->count();
        
        $usageStats = DB::table('coupon_usages')
            ->selectRaw('COUNT(*) as total_usage, SUM(discount_amount) as total_discount')
            ->first();

        return [
            'summary' => [
                'total_coupons' => $totalCoupons,
                'active_coupons' => $activeCoupons,
                'expired_coupons' => $expiredCoupons,
                'total_usage' => $usageStats->total_usage ?? 0,
                'total_discount_given' => round($usageStats->total_discount ?? 0, 2),
            ],
        ];
    }

    public function getDashboardStats(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');
        
        $todaySales = $this->orderRepository->query()
            ->whereDate('created_at', $today)
            ->sum('total_amount');
        
        $todayOrders = $this->orderRepository->query()
            ->whereDate('created_at', $today)
            ->count();
        
        $pendingOrders = $this->orderRepository->query()
            ->where('status', 'pending')
            ->count();
        
        $lowStockProducts = $this->variantRepository->query()
            ->whereColumn('stock_quantity', '<=', 'low_stock_alert')
            ->count();
        
        $totalCustomers = $this->userRepository->query()->count();
        $totalProducts = $this->productRepository->query()->count();
        
        $monthlySales = $this->orderRepository->query()
            ->where('created_at', 'like', $thisMonth . '%')
            ->sum('total_amount');

        return [
            'today_sales' => round($todaySales, 2),
            'today_orders' => $todayOrders,
            'pending_orders' => $pendingOrders,
            'low_stock_products' => $lowStockProducts,
            'total_customers' => $totalCustomers,
            'total_products' => $totalProducts,
            'monthly_sales' => round($monthlySales, 2),
        ];
    }

    public function exportToExcel(ReportType $type, ReportFilterDTO $filters): string
    {
        $data = match ($type) {
            ReportType::SALES => $this->generateSalesReport($filters),
            ReportType::INVENTORY => $this->generateInventoryReport(),
            ReportType::CUSTOMER => $this->generateCustomerReport($filters),
            ReportType::COUPON => $this->generateCouponReport($filters),
            default => [],
        };

        $export = new \App\Modules\Report\src\Exports\ReportExport($data, $type);
        $filename = "{$type->value}_report_" . now()->format('Y-m-d_His') . ".xlsx";
        
        Excel::store($export, "reports/{$filename}", 'public');
        
        return $filename;
    }
}
