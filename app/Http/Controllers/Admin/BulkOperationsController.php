<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Services\StockManagerService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class BulkOperationsController extends Controller
{
    protected StockManagerService $stockManager;
    protected PricingService $pricingService;

    public function __construct(StockManagerService $stockManager, PricingService $pricingService)
    {
        $this->stockManager = $stockManager;
        $this->pricingService = $pricingService;
    }

    /**
     * Show bulk stock update page
     */
    public function bulkStock()
    {
        $products = Product::with(['variants.attributeValues.attribute'])
            ->where('product_type', 'variable')
            ->orWhere(function($q) {
                $q->where('product_type', 'simple')->where('manage_stock', true);
            })
            ->get();

        return view('admin.bulk-operations.stock', compact('products'));
    }

    /**
     * Process bulk stock update
     */
    public function processBulkStock(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'required|in:add,subtract,set',
            'updates' => 'required|array',
            'updates.*.id' => 'required|integer',
            'updates.*.type' => 'required|in:product,variant',
            'updates.*.quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($validated['updates'] as $update) {
            try {
                if ($update['type'] === 'variant') {
                    $variant = Variant::find($update['id']);
                    if (!$variant) {
                        $results['failed'][] = ['id' => $update['id'], 'reason' => 'Variant not found'];
                        continue;
                    }

                    $success = $this->stockManager->adjustStock(
                        $variant,
                        $validated['operation'] === 'subtract' ? -$update['quantity'] : $update['quantity'],
                        $validated['reason']
                    );

                    if ($validated['operation'] === 'set') {
                        $success = $this->stockManager->setStock($variant, $update['quantity'], $validated['reason']);
                    }

                    if ($success) {
                        $results['success'][] = ['id' => $variant->id, 'sku' => $variant->sku, 'new_stock' => $variant->fresh()->stock_quantity];
                    } else {
                        $results['failed'][] = ['id' => $update['id'], 'reason' => 'Update failed'];
                    }
                } else {
                    // Simple product
                    $product = Product::find($update['id']);
                    if (!$product || $product->product_type !== 'simple') {
                        $results['failed'][] = ['id' => $update['id'], 'reason' => 'Product not found or not simple type'];
                        continue;
                    }

                    $oldStock = $product->stock_quantity;
                    $newStock = match($validated['operation']) {
                        'add' => $oldStock + $update['quantity'],
                        'subtract' => max(0, $oldStock - $update['quantity']),
                        'set' => $update['quantity'],
                        default => $oldStock,
                    };

                    // Log movement
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
                    }

                    $product->update([
                        'stock_quantity' => $newStock,
                        'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                    ]);

                    $results['success'][] = ['id' => $product->id, 'sku' => $product->sku, 'new_stock' => $newStock];
                }
            } catch (\Exception $e) {
                $results['failed'][] = ['id' => $update['id'], 'reason' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($results['success']),
            'failed' => count($results['failed']),
            'details' => $results,
        ]);
    }

    /**
     * Show bulk price update page
     */
    public function bulkPrice()
    {
        $products = Product::with(['variants.attributeValues.attribute'])
            ->get();

        return view('admin.bulk-operations.price', compact('products'));
    }

    /**
     * Process bulk price update
     */
    public function processBulkPrice(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'required|in:increase,decrease,set',
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'updates' => 'required|array',
            'updates.*.id' => 'required|integer',
            'updates.*.type' => 'required|in:product,variant',
        ]);

        $results = [
            'success' => [],
            'failed' => [],
        ];

        $variantIds = [];

        foreach ($validated['updates'] as $update) {
            if ($update['type'] === 'variant') {
                $variantIds[] = $update['id'];
            } else {
                // For simple products, we update base_price
                $product = Product::find($update['id']);
                if ($product && $product->product_type === 'simple') {
                    $oldPrice = $product->base_price;
                    $newPrice = $this->calculateNewPrice($oldPrice, $validated['operation'], $validated['value'], $validated['type']);
                    
                    $product->update(['base_price' => $newPrice]);
                    
                    $results['success'][] = [
                        'id' => $product->id,
                        'type' => 'product',
                        'name' => $product->name,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                    ];
                }
            }
        }

        // Process variant price updates using PricingService
        if (!empty($variantIds)) {
            $updatedCount = $this->pricingService->bulkUpdatePrices(
                $variantIds,
                $validated['operation'],
                $validated['value'],
                $validated['type']
            );

            foreach ($variantIds as $variantId) {
                $variant = Variant::find($variantId);
                if ($variant) {
                    $results['success'][] = [
                        'id' => $variant->id,
                        'type' => 'variant',
                        'sku' => $variant->sku,
                        'new_price' => $variant->fresh()->price,
                    ];
                }
            }
        }

        return response()->json([
            'success' => count($results['success']),
            'failed' => count($results['failed']),
            'details' => $results,
        ]);
    }

    /**
     * Calculate new price based on operation
     */
    private function calculateNewPrice(float $currentPrice, string $operation, float $value, string $type): float
    {
        return match($operation) {
            'increase' => $type === 'percentage' 
                ? $currentPrice * (1 + $value / 100) 
                : $currentPrice + $value,
            'decrease' => $type === 'percentage'
                ? max(0, $currentPrice * (1 - $value / 100))
                : max(0, $currentPrice - $value),
            'set' => $value,
            default => $currentPrice,
        };
    }

    /**
     * Toggle status for multiple products/variants
     */
    public function bulkToggleStatus(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|in:product,variant',
            'status' => 'required|boolean',
        ]);

        $results = ['products' => 0, 'variants' => 0];

        foreach ($validated['items'] as $item) {
            if ($item['type'] === 'product') {
                Product::where('id', $item['id'])->update(['is_active' => $validated['status']]);
                $results['products']++;
            } else {
                Variant::where('id', $item['id'])->update(['is_active' => $validated['status']]);
                $results['variants']++;
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $results,
        ]);
    }

    /**
     * Delete multiple products/variants
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|in:product,variant',
        ]);

        $results = ['success' => [], 'failed' => []];

        foreach ($validated['items'] as $item) {
            try {
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product && !$product->variants()->whereHas('orderItems')->exists()) {
                        $product->delete();
                        $results['success'][] = ['id' => $item['id'], 'type' => 'product', 'name' => $product->name];
                    } else {
                        $results['failed'][] = ['id' => $item['id'], 'reason' => 'Has orders or not found'];
                    }
                } else {
                    $variant = Variant::find($item['id']);
                    if ($variant && $variant->orderItems()->count() === 0) {
                        $variant->attributeValues()->detach();
                        $variant->delete();
                        $results['success'][] = ['id' => $item['id'], 'type' => 'variant', 'sku' => $variant->sku];
                    } else {
                        $results['failed'][] = ['id' => $item['id'], 'reason' => 'Has orders or not found'];
                    }
                }
            } catch (\Exception $e) {
                $results['failed'][] = ['id' => $item['id'], 'reason' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($results['success']),
            'failed' => count($results['failed']),
            'details' => $results,
        ]);
    }

    /**
     * Export variants to CSV
     */
    public function exportVariants(Request $request)
    {
        $variants = Variant::with(['product', 'attributeValues.attribute'])
            ->when($request->filled('product_id'), function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="variants-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($variants) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Product Name', 'SKU', 'Barcode', 'Price', 'Compare Price', 'Cost Price', 'Stock Qty', 'Stock Status', 'Weight', 'Is Active', 'Attributes']);
            
            // Data
            foreach ($variants as $variant) {
                fputcsv($file, [
                    $variant->product->name,
                    $variant->sku,
                    $variant->barcode,
                    $variant->price,
                    $variant->compare_price,
                    $variant->cost_price,
                    $variant->stock_quantity,
                    $variant->stock_status,
                    $variant->weight,
                    $variant->is_active ? 'Yes' : 'No',
                    $variant->attribute_text,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import variants from CSV
     */
    public function importVariants(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header
        $header = fgetcsv($handle);
        
        $results = ['imported' => 0, 'updated' => 0, 'failed' => []];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            try {
                $data = array_combine($header, $row);
                
                // Find variant by SKU
                $variant = Variant::where('sku', $data['SKU'])->first();
                
                if ($variant) {
                    // Update existing
                    $variant->update([
                        'barcode' => $data['Barcode'] ?? $variant->barcode,
                        'price' => $data['Price'] ?? $variant->price,
                        'compare_price' => $data['Compare Price'] ?? $variant->compare_price,
                        'cost_price' => $data['Cost Price'] ?? $variant->cost_price,
                        'stock_quantity' => $data['Stock Qty'] ?? $variant->stock_quantity,
                        'stock_status' => $data['Stock Status'] ?? $variant->stock_status,
                        'weight' => $data['Weight'] ?? $variant->weight,
                        'is_active' => ($data['Is Active'] ?? 'Yes') === 'Yes',
                    ]);
                    $results['updated']++;
                } else {
                    $results['failed'][] = ['row' => $rowNumber, 'reason' => 'Variant not found: ' . $data['SKU']];
                }
            } catch (\Exception $e) {
                $results['failed'][] = ['row' => $rowNumber, 'reason' => $e->getMessage()];
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'imported' => $results['imported'],
            'updated' => $results['updated'],
            'failed' => count($results['failed']),
            'details' => $results,
        ]);
    }
}
