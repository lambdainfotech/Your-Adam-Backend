<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Services\StockManagerService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    use ApiResponse;
    protected StockManagerService $stockManager;

    public function __construct(StockManagerService $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    /**
     * Get inventory summary
     */
    public function summary(): JsonResponse
    {
        $summary = [
            'total_products' => Product::count(),
            'simple_products' => Product::simple()->count(),
            'variable_products' => Product::variable()->count(),
            'total_variants' => Variant::count(),
            'low_stock_count' => count($this->stockManager->getLowStockItems(1000)),
            'out_of_stock_count' => count($this->stockManager->getOutOfStockItems(1000)),
            'valuation' => $this->stockManager->getInventoryValuation(),
        ];

        return $this->success($summary, 'Inventory summary retrieved successfully');
    }

    /**
     * Get low stock items
     */
    public function lowStock(): JsonResponse
    {
        $items = $this->stockManager->getLowStockItems(100);

        return $this->success([
            'count' => count($items),
            'items' => $items,
        ], 'Low stock items retrieved successfully');
    }

    /**
     * Get out of stock items
     */
    public function outOfStock(): JsonResponse
    {
        $items = $this->stockManager->getOutOfStockItems(100);

        return $this->success([
            'count' => count($items),
            'items' => $items,
        ], 'Low stock items retrieved successfully');
    }

    /**
     * Get inventory movements
     */
    public function movements(Request $request): JsonResponse
    {
        $query = \App\Models\InventoryMovement::with(['product', 'variant', 'creator']);

        if ($request->filled('product_id')) {
            $query->forProduct($request->product_id);
        }

        if ($request->filled('variant_id')) {
            $query->forVariant($request->variant_id);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('days')) {
            $query->recent($request->days);
        }

        $movements = $query->latest()->paginate($request->input('per_page', 20));

        return $this->paginated($movements, 'Inventory movements retrieved successfully');
    }

    /**
     * Get stock history for a variant
     */
    public function variantHistory(Request $request, Variant $variant): JsonResponse
    {
        $movements = $variant->inventoryMovements()
            ->with('creator')
            ->latest()
            ->paginate($request->input('per_page', 20));

        return $this->success([
            'variant' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $variant->product->name,
            ],
            'movements' => $movements->items(),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
                'from' => $movements->firstItem(),
                'to' => $movements->lastItem(),
            ],
        ], 'Variant stock history retrieved successfully');
    }

    /**
     * Update stock (admin only)
     */
    public function updateStock(Request $request, Variant $variant): JsonResponse
    {
        $request->validate([
            'operation' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $operation = $request->input('operation');
        $quantity = $request->input('quantity');
        $reason = $request->input('reason');

        $success = false;

        switch ($operation) {
            case 'add':
                $success = $this->stockManager->adjustStock($variant, $quantity, $reason);
                break;
            case 'subtract':
                $success = $this->stockManager->adjustStock($variant, -$quantity, $reason);
                break;
            case 'set':
                $success = $this->stockManager->setStock($variant, $quantity, $reason);
                break;
        }

        if (!$success) {
            return $this->error('Failed to update stock', 500);
        }

        return $this->success([
            'variant_id' => $variant->id,
            'new_stock' => $variant->fresh()->stock_quantity,
            'stock_status' => $variant->fresh()->stock_status,
        ], 'Stock updated successfully');
    }

    /**
     * Get inventory valuation
     */
    public function valuation(): JsonResponse
    {
        $valuation = $this->stockManager->getInventoryValuation();
        $categoryBreakdown = $this->stockManager->getCategoryValuationDetailed();

        return $this->success([
            'total_valuation' => $valuation,
            'by_category' => $categoryBreakdown,
        ], 'Inventory valuation retrieved successfully');
    }

    /**
     * Bulk stock update (admin only)
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'operation' => 'required|in:add,subtract,set',
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $results = $this->stockManager->bulkUpdateStock(
            $request->items,
            $request->operation,
            $request->reason
        );

        return $this->success($results, 'Bulk stock update completed successfully');
    }
}
