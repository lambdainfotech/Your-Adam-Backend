<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold',
        'manage_stock',
        'weight',
        'is_active',
        'position',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'manage_stock' => 'boolean',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_status', 'out_of_stock');
    }

    public function scopeOnBackorder(Builder $query): Builder
    {
        return $query->where('stock_status', 'on_backorder');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('manage_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);
    }

    public function scopeManageStock(Builder $query): Builder
    {
        return $query->where('manage_stock', true);
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Attribute Accessors
    public function getFinalPriceAttribute(): float
    {
        // If variant has its own price, use it
        if ($this->price !== null && $this->price > 0) {
            return (float) $this->price;
        }
        // Otherwise fall back to product price
        return $this->product->final_price;
    }

    public function getAttributeTextAttribute(): string
    {
        return $this->attributeValues
            ->map(fn ($av) => $av->attribute->name . ': ' . $av->value)
            ->join(', ');
    }

    public function getAttributeTextShortAttribute(): string
    {
        return $this->attributeValues
            ->map(fn ($av) => $av->value)
            ->join(' / ');
    }

    public function getIsInStockAttribute(): bool
    {
        if (!$this->manage_stock) {
            return $this->stock_status === 'in_stock';
        }
        return $this->stock_quantity > 0 && $this->stock_status === 'in_stock';
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->manage_stock && 
               $this->stock_quantity > 0 && 
               $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function getStockBadgeAttribute(): array
    {
        if ($this->is_in_stock) {
            if ($this->is_low_stock) {
                return ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Low Stock (' . $this->stock_quantity . ')'];
            }
            return ['class' => 'bg-green-100 text-green-800', 'text' => 'In Stock (' . $this->stock_quantity . ')'];
        }
        if ($this->stock_status === 'on_backorder') {
            return ['class' => 'bg-blue-100 text-blue-800', 'text' => 'On Backorder'];
        }
        return ['class' => 'bg-red-100 text-red-800', 'text' => 'Out of Stock'];
    }

    // Helper Methods
    public function updateStockStatus(): void
    {
        if ($this->manage_stock) {
            if ($this->stock_quantity > 0) {
                $this->stock_status = 'in_stock';
            } else {
                $this->stock_status = 'out_of_stock';
            }
            $this->saveQuietly();
        }
    }

    public function adjustStock(int $adjustment, string $reason = '', ?int $referenceId = null, ?string $referenceType = null): void
    {
        $oldStock = $this->stock_quantity;
        $newStock = max(0, $oldStock + $adjustment);
        
        // Log the movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'variant_id' => $this->id,
            'movement_type' => $adjustment > 0 ? 'in' : 'out',
            'quantity' => abs($adjustment),
            'reason' => $reason ?: 'Stock adjustment',
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'created_by' => auth()->id(),
        ]);

        // Update stock
        $this->stock_quantity = $newStock;
        $this->updateStockStatus();
        $this->saveQuietly();
    }

    public function setStock(int $newStock, string $reason = '', ?int $referenceId = null, ?string $referenceType = null): void
    {
        $oldStock = $this->stock_quantity;
        
        // Log the movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'variant_id' => $this->id,
            'movement_type' => 'adjustment',
            'quantity' => abs($newStock - $oldStock),
            'reason' => $reason ?: 'Stock set to ' . $newStock,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'created_by' => auth()->id(),
        ]);

        // Update stock
        $this->stock_quantity = $newStock;
        $this->updateStockStatus();
        $this->saveQuietly();
    }

    public function canPurchase(int $quantity = 1): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if (!$this->is_in_stock) {
            return false;
        }
        if ($this->manage_stock && $this->stock_quantity < $quantity) {
            return false;
        }
        return true;
    }
}
