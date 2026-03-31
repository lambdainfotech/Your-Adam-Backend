<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;

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
            ->get(['id', 'name', 'sku_prefix']);
        
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
            'items.*.variant_id' => 'required|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $successCount = 0;
        $errorMessages = [];

        foreach ($validated['items'] as $item) {
            $variant = Variant::find($item['variant_id']);
            
            if ($variant) {
                $oldStock = $variant->stock_quantity;
                $variant->stock_quantity += $item['quantity'];
                $variant->save();
                $successCount++;
            } else {
                $errorMessages[] = "Variant not found for item.";
            }
        }

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
            'variants' => $variants
        ]);
    }
}
