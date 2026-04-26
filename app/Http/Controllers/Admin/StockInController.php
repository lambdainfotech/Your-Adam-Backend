<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    /**
     * Show bulk stock in form
     */
    public function bulkCreate()
    {
        $products = Product::where('is_active', true)
            ->with(['variants' => function($query) {
                $query->select('id', 'product_id', 'sku', 'stock_quantity');
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'sku_prefix', 'has_variants', 'stock_quantity']);
        
        return view('admin.stock-in.bulk', compact('products'));
    }

    /**
     * Store bulk stock in
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:variants,id',
            'items.*.has_variants' => 'required|in:0,1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $successCount = 0;
        $errorMessages = [];

        DB::transaction(function () use ($validated, &$successCount, &$errorMessages) {
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                $errorMessages[] = "Product not found.";
                continue;
            }

            // If product has variants, update variant stock
            if ($item['has_variants'] == '1') {
                if (empty($item['variant_id'])) {
                    $errorMessages[] = "Variant required for product: {$product->name}";
                    continue;
                }

                $variant = Variant::find($item['variant_id']);
                
                if ($variant) {
                    $oldStock = $variant->stock_quantity;
                    $variant->stock_quantity += $item['quantity'];
                    $variant->save();
                    
                    // Log inventory movement
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'movement_type' => InventoryMovement::TYPE_IN,
                        'quantity' => $item['quantity'],
                        'reason' => 'Stock In: ' . ($validated['reference_no'] ?? 'Bulk'),
                        'stock_before' => $oldStock,
                        'stock_after' => $variant->stock_quantity,
                        'created_by' => auth()->id(),
                    ]);
                    
                    $successCount++;
                } else {
                    $errorMessages[] = "Variant not found for: {$product->name}";
                }
            } else {
                // Simple product - update product stock directly
                $oldStock = $product->stock_quantity;
                $product->stock_quantity += $item['quantity'];
                $product->save();
                
                // Log inventory movement
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'movement_type' => InventoryMovement::TYPE_IN,
                    'quantity' => $item['quantity'],
                    'reason' => 'Stock In: ' . ($validated['reference_no'] ?? 'Bulk'),
                    'stock_before' => $oldStock,
                    'stock_after' => $product->stock_quantity,
                    'created_by' => auth()->id(),
                ]);
                
                $successCount++;
            }
        }
        });

        if ($successCount > 0) {
            $message = "Successfully added stock for {$successCount} item(s).";
            if (!empty($errorMessages)) {
                $message .= " Some items failed: " . implode(', ', $errorMessages);
            }
            
            return redirect()->route('admin.stock-in.bulk')
                ->with('success', $message);
        }

        return redirect()->back()
            ->with('error', 'Failed to add stock. Please try again.')
            ->withInput();
    }

    /**
     * Get variants for a product (AJAX)
     */
    public function getVariants(Product $product)
    {
        $variants = $product->variants()
            ->select('id', 'sku', 'stock_quantity', 'price')
            ->get();
        
        return response()->json([
            'success' => true,
            'variants' => $variants,
            'has_variants' => $product->has_variants,
            'product_stock' => $product->stock_quantity,
        ]);
    }
}
