<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockManagerService
{
    /**
     * Adjust stock quantity
     */
    public function adjustStock(
        Variant $variant,
        int $adjustment,
        string $reason = '',
        ?int $referenceId = null,
        ?string $referenceType = null,
        ?int $userId = null
    ): bool {
        try {
            DB::beginTransaction();

            $oldStock = $variant->stock_quantity;
            $newStock = max(0, $oldStock + $adjustment);

            // Create inventory movement log
            InventoryMovement::create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'movement_type' => $adjustment > 0 ? InventoryMovement::TYPE_IN : InventoryMovement::TYPE_OUT,
                'quantity' => abs($adjustment),
                'reason' => $reason ?: 'Stock adjustment',
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Update variant stock
            $variant->stock_quantity = $newStock;
            $variant->updateStockStatus();
            $variant->save();

            // Update product total stock
            $this->updateProductTotalStock($variant->product);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock adjustment failed: ' . $e->getMessage(), [
                'variant_id' => $variant->id,
                'adjustment' => $adjustment,
            ]);
            return false;
        }
    }

    /**
     * Set stock to a specific quantity
     */
    public function setStock(
        Variant $variant,
        int $newStock,
        string $reason = '',
        ?int $referenceId = null,
        ?string $referenceType = null,
        ?int $userId = null
    ): bool {
        try {
            DB::beginTransaction();

            $oldStock = $variant->stock_quantity;

            // Create inventory movement log
            InventoryMovement::create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'movement_type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => abs($newStock - $oldStock),
                'reason' => $reason ?: "Stock set to {$newStock}",
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Update variant stock
            $variant->stock_quantity = $newStock;
            $variant->updateStockStatus();
            $variant->save();

            // Update product total stock
            $this->updateProductTotalStock($variant->product);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock set failed: ' . $e->getMessage(), [
                'variant_id' => $variant->id,
                'new_stock' => $newStock,
            ]);
            return false;
        }
    }

    /**
     * Record sale transaction
     */
    public function recordSale(
        Variant $variant,
        int $quantity,
        int $orderId,
        ?int $userId = null
    ): bool {
        return $this->adjustStock(
            $variant,
            -$quantity,
            'Sale - Order #' . $orderId,
            $orderId,
            'App\\Models\\Order',
            $userId
        );
    }

    /**
     * Record return transaction
     */
    public function recordReturn(
        Variant $variant,
        int $quantity,
        int $orderId,
        ?int $userId = null
    ): bool {
        return $this->adjustStock(
            $variant,
            $quantity,
            'Return - Order #' . $orderId,
            $orderId,
            'App\\Models\\Order',
            $userId
        );
    }

    /**
     * Bulk stock update
     */
    public function bulkUpdateStock(array $updates, string $operation = 'add', string $reason = ''): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($updates as $update) {
            $variant = Variant::find($update['variant_id']);
            
            if (!$variant) {
                $results['failed'][] = [
                    'variant_id' => $update['variant_id'],
                    'reason' => 'Variant not found',
                ];
                continue;
            }

            $quantity = (int) $update['quantity'];

            switch ($operation) {
                case 'add':
                    $success = $this->adjustStock($variant, $quantity, $reason);
                    break;
                case 'subtract':
                    $success = $this->adjustStock($variant, -$quantity, $reason);
                    break;
                case 'set':
                    $success = $this->setStock($variant, $quantity, $reason);
                    break;
                default:
                    $success = false;
            }

            if ($success) {
                $results['success'][] = [
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'new_stock' => $variant->fresh()->stock_quantity,
                ];
            } else {
                $results['failed'][] = [
                    'variant_id' => $variant->id,
                    'reason' => 'Update failed',
                ];
            }
        }

        return $results;
    }

    /**
     * Update product's total stock
     */
    public function updateProductTotalStock(Product $product): void
    {
        if ($product->product_type === 'simple') {
            // For simple products, just ensure stock status matches
            $product->updateStockStatus();
        } else {
            // For variable products, sum all variants
            $totalStock = $product->variants()->sum('stock_quantity');
            $product->update(['total_stock' => $totalStock]);
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(int $limit = 50): array
    {
        // Simple products with low stock
        $simpleProducts = Product::simple()
            ->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $product->stock_quantity,
                    'threshold' => $product->low_stock_threshold,
                    'category' => $product->category?->name,
                ];
            });

        // Variants with low stock
        $variants = Variant::where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->with(['product', 'product.category', 'attributeValues'])
            ->limit($limit)
            ->get()
            ->map(function ($variant) {
                return [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => ($variant->product?->name ?? 'Unknown Product') . ' - ' . $variant->attribute_text_short,
                    'sku' => $variant->sku,
                    'stock' => $variant->stock_quantity,
                    'threshold' => $variant->low_stock_threshold,
                    'category' => $variant->product?->category?->name,
                ];
            });

        return collect($simpleProducts)->merge(collect($variants))
            ->sortBy('stock')
            ->values()
            ->toArray();
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems(int $limit = 50): array
    {
        // Simple products out of stock
        $simpleProducts = Product::simple()
            ->where(function ($q) {
                $q->where('manage_stock', true)->where('stock_quantity', '<=', 0)
                  ->orWhere('stock_status', 'out_of_stock');
            })
            ->with('category')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $product->stock_quantity,
                    'category' => $product->category?->name,
                ];
            });

        // Variants out of stock
        $variants = Variant::where(function ($q) {
                $q->where('manage_stock', true)->where('stock_quantity', '<=', 0)
                  ->orWhere('stock_status', 'out_of_stock');
            })
            ->with(['product', 'product.category'])
            ->limit($limit)
            ->get()
            ->map(function ($variant) {
                return [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name . ' - ' . $variant->attribute_text_short,
                    'sku' => $variant->sku,
                    'stock' => $variant->stock_quantity,
                    'category' => $variant->product->category?->name,
                ];
            });

        return $simpleProducts->merge($variants)
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * Check if stock is available
     */
    public function checkAvailability(Variant $variant, int $requestedQuantity): array
    {
        $available = true;
        $message = 'In stock';

        if (!$variant->is_active) {
            $available = false;
            $message = 'Product is not active';
        } elseif (!$variant->is_in_stock) {
            $available = false;
            $message = 'Out of stock';
        } elseif ($variant->manage_stock && $variant->stock_quantity < $requestedQuantity) {
            $available = false;
            $message = "Only {$variant->stock_quantity} available";
        }

        return [
            'available' => $available,
            'message' => $message,
            'available_quantity' => $variant->is_in_stock 
                ? ($variant->manage_stock ? $variant->stock_quantity : 999999) 
                : 0,
        ];
    }

    /**
     * Get stock valuation (inventory value)
     */
    public function getInventoryValuation(): array
    {
        // Simple products valuation
        $simpleValuation = Product::simple()
            ->where('manage_stock', true)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->reduce(function ($carry, $product) {
                $cost = $product->cost_price ?? $product->base_price * 0.6; // Estimate if no cost
                return $carry + ($cost * $product->stock_quantity);
            }, 0);

        // Variants valuation
        $variantValuation = Variant::where('manage_stock', true)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->reduce(function ($carry, $variant) {
                $cost = $variant->cost_price ?? $variant->price ?? $variant->product->base_price * 0.6;
                return $carry + ($cost * $variant->stock_quantity);
            }, 0);

        return [
            'simple_products' => round($simpleValuation, 2),
            'variants' => round($variantValuation, 2),
            'total' => round($simpleValuation + $variantValuation, 2),
        ];
    }
}
