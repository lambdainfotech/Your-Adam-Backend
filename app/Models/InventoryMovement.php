<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'movement_type',
        'quantity',
        'reason',
        'reference_id',
        'reference_type',
        'stock_before',
        'stock_after',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    // Movement types
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_SALE = 'sale';
    public const TYPE_RETURN = 'return';
    public const TYPE_TRANSFER = 'transfer';

    // Scopes
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant(Builder $query, int $variantId): Builder
    {
        return $query->where('variant_id', $variantId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('movement_type', $type);
    }

    public function scopeStockIn(Builder $query): Builder
    {
        return $query->whereIn('movement_type', [self::TYPE_IN, self::TYPE_RETURN]);
    }

    public function scopeStockOut(Builder $query): Builder
    {
        return $query->whereIn('movement_type', [self::TYPE_OUT, self::TYPE_SALE]);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Attribute Accessors
    public function getMovementTypeLabelAttribute(): string
    {
        return match($this->movement_type) {
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_SALE => 'Sale',
            self::TYPE_RETURN => 'Return',
            self::TYPE_TRANSFER => 'Transfer',
            default => ucfirst($this->movement_type),
        };
    }

    public function getMovementTypeBadgeAttribute(): array
    {
        return match($this->movement_type) {
            self::TYPE_IN, self::TYPE_RETURN => ['class' => 'bg-green-100 text-green-800', 'text' => $this->movement_type_label],
            self::TYPE_OUT, self::TYPE_SALE => ['class' => 'bg-red-100 text-red-800', 'text' => $this->movement_type_label],
            self::TYPE_ADJUSTMENT => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => $this->movement_type_label],
            self::TYPE_TRANSFER => ['class' => 'bg-blue-100 text-blue-800', 'text' => $this->movement_type_label],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => $this->movement_type_label],
        };
    }

    public function getQuantityWithSignAttribute(): string
    {
        $sign = in_array($this->movement_type, [self::TYPE_IN, self::TYPE_RETURN]) ? '+' : '-';
        return $sign . $this->quantity;
    }

    public function getReferenceUrlAttribute(): ?string
    {
        if (!$this->reference_id || !$this->reference_type) {
            return null;
        }

        return match($this->reference_type) {
            'App\Models\Order' => route('admin.orders.show', $this->reference_id),
            default => null,
        };
    }

    public function getReferenceNumberAttribute(): ?string
    {
        if (!$this->reference_id || !$this->reference_type) {
            return null;
        }

        $model = $this->reference_type::find($this->reference_id);
        
        return match($this->reference_type) {
            'App\Models\Order' => $model?->order_number,
            default => '#' . $this->reference_id,
        };
    }

    // Helper Methods
    public static function logMovement(
        int $productId,
        ?int $variantId,
        string $type,
        int $quantity,
        string $reason = '',
        ?int $referenceId = null,
        ?string $referenceType = null
    ): self {
        $variant = $variantId ? Variant::find($variantId) : null;
        $stockBefore = $variant ? $variant->stock_quantity : 0;
        $stockAfter = $stockBefore + $quantity;
        
        return self::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'created_by' => auth()->id(),
        ]);
    }
}
