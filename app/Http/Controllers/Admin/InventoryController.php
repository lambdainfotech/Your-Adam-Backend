<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Services\StockManagerService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected StockManagerService $stockManager;

    public function __construct(StockManagerService $stockManager)
    {
        $this->stockManager = $stockManager;
    }

    public function index(Request $request)
    {
        $query = Product::with(['variants', 'category']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('sku_prefix', 'like', "%{$search}%");
            });
        }
        
        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->where('stock_status', 'out_of_stock')
                          ->orWhere('product_type', 'variable')
                          ->whereDoesntHave('variants', function($qv) {
                              $qv->where('stock_status', 'in_stock');
                          });
                    });
                    break;
                case 'low_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->where('manage_stock', true)
                          ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                          ->where('stock_quantity', '>', 0);
                    })->orWhere(function($q) {
                        $q->where('product_type', 'variable')
                          ->whereHas('variants', function($qv) {
                              $qv->where('manage_stock', true)
                                 ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                                 ->where('stock_quantity', '>', 0);
                          });
                    });
                    break;
                case 'in_stock':
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->where('stock_status', 'in_stock')
                          ->orWhere('product_type', 'variable')
                          ->whereHas('variants', function($qv) {
                              $qv->where('stock_status', 'in_stock');
                          });
                    });
                    break;
            }
        }
        
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        // Summary counts
        $summary = [
            'total' => Product::count(),
            'out_of_stock' => count($this->stockManager->getOutOfStockItems(1000)),
            'low_stock' => count($this->stockManager->getLowStockItems(1000)),
            'valuation' => $this->stockManager->getInventoryValuation(),
        ];
        
        return view('admin.inventory.index', compact('products', 'summary'));
    }
    
    public function edit(Product $product)
    {
        $product->load(['variants.attributeValues.attribute', 'inventoryMovements' => function($q) {
            $q->with('creator')->latest()->limit(20);
        }]);
        
        return view('admin.inventory.edit', compact('product'));
    }
    
    public function update(Request $request, Product $product)
    {
        if ($product->product_type === 'simple') {
            $validated = $request->validate([
                'stock_quantity' => 'required|integer|min:0',
                'reason' => 'required|string|max:255',
            ]);

            $oldStock = $product->stock_quantity;
            $newStock = $validated['stock_quantity'];

            if ($newStock !== $oldStock) {
                \App\Models\InventoryMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => $newStock > $oldStock ? 'in' : 'out',
                    'quantity' => abs($newStock - $oldStock),
                    'reason' => $validated['reason'],
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'created_by' => auth()->id(),
                ]);

                $product->update([
                    'stock_quantity' => $newStock,
                    'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                ]);
            }
        } else {
            $validated = $request->validate([
                'variants' => 'required|array',
                'variants.*.id' => 'required|exists:variants,id',
                'variants.*.stock_quantity' => 'required|integer|min:0',
                'variants.*.price' => 'nullable|numeric|min:0',
                'reason' => 'required|string|max:255',
            ]);
            
            foreach ($validated['variants'] as $variantData) {
                $variant = Variant::find($variantData['id']);
                if ($variant && $variant->product_id === $product->id) {
                    $oldStock = $variant->stock_quantity;
                    $newStock = $variantData['stock_quantity'];

                    // Log stock change
                    if ($newStock !== $oldStock) {
                        \App\Models\InventoryMovement::create([
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'movement_type' => $newStock > $oldStock ? 'in' : 'out',
                            'quantity' => abs($newStock - $oldStock),
                            'reason' => $validated['reason'],
                            'stock_before' => $oldStock,
                            'stock_after' => $newStock,
                            'created_by' => auth()->id(),
                        ]);
                    }

                    $variant->update([
                        'stock_quantity' => $newStock,
                        'price' => $variantData['price'] ?? $variant->price,
                        'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.inventory.index')
            ->with('success', 'Inventory updated successfully.');
    }
    
    public function adjustStock(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);
        
        $success = $this->stockManager->adjustStock(
            $variant,
            $validated['adjustment'],
            $validated['reason']
        );
        
        if ($success) {
            return redirect()->back()
                ->with('success', "Stock adjusted successfully. New stock: {$variant->fresh()->stock_quantity}");
        }
        
        return redirect()->back()
            ->with('error', 'Failed to adjust stock.');
    }

    /**
     * Get stock history for a variant
     */
    public function history(Request $request, Variant $variant)
    {
        $movements = $variant->inventoryMovements()
            ->with('creator')
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'movements' => $movements,
            ]);
        }

        return view('admin.inventory.history', compact('variant', 'movements'));
    }

    /**
     * Low stock alerts page
     */
    public function lowStockAlerts()
    {
        $lowStockItems = $this->stockManager->getLowStockItems(100);
        
        return view('admin.inventory.low-stock', compact('lowStockItems'));
    }

    /**
     * Inventory valuation report
     */
    public function valuation()
    {
        $valuation = $this->stockManager->getInventoryValuation();
        $categoryValuation = $this->stockManager->getCategoryValuation();

        return view('admin.inventory.valuation', compact('valuation', 'categoryValuation'));
    }

    /**
     * Stock movement report
     */
    public function movements(Request $request)
    {
        $query = \App\Models\InventoryMovement::with(['product', 'variant', 'creator']);

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        if ($request->filled('product')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->product}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50);

        // Summary
        $summary = [
            'total_in' => \App\Models\InventoryMovement::stockIn()->sum('quantity'),
            'total_out' => \App\Models\InventoryMovement::stockOut()->sum('quantity'),
            'today_in' => \App\Models\InventoryMovement::stockIn()->whereDate('created_at', today())->sum('quantity'),
            'today_out' => \App\Models\InventoryMovement::stockOut()->whereDate('created_at', today())->sum('quantity'),
        ];

        return view('admin.inventory.movements', compact('movements', 'summary'));
    }
}
