<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'product_type',
        'base_price',
        'compare_price',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'cost_price',
        'sku',
        'sku_prefix',
        'barcode',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold',
        'weight',
        'status',
        'is_active',
        'is_featured',
        'has_variants',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'manage_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_variants' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSimple($query)
    {
        return $query->where('product_type', 'simple');
    }

    public function scopeVariable($query)
    {
        return $query->where('product_type', 'variable');
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('product_type', 'simple')
              ->where('stock_status', 'in_stock')
              ->orWhere('product_type', 'variable')
              ->whereHas('variants', function($qv) {
                  $qv->where('stock_status', 'in_stock');
              });
        });
    }

    public function scopeOnSale($query)
    {
        $now = Carbon::now();
        return $query->whereNotNull('sale_price')
            ->where(function($q) use ($now) {
                $q->whereNull('sale_start_date')->orWhere('sale_start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('sale_end_date')->orWhere('sale_end_date', '>=', $now);
            });
    }

    public function scopeLowStock($query)
    {
        return $query->where(function($q) {
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
    }

    public function scopeOutOfStock($query)
    {
        return $query->where(function($q) {
            $q->where('product_type', 'simple')
              ->where('stock_status', 'out_of_stock');
        })->orWhere(function($q) {
            $q->where('product_type', 'variable')
              ->whereDoesntHave('variants', function($qv) {
                  $qv->where('stock_status', 'in_stock');
              });
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class)->orderBy('position');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(Variant::class)->where('is_active', true)->orderBy('position');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function variationAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withTimestamps();
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products')
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function sizeChart(): HasOne
    {
        return $this->hasOne(SizeChart::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Attribute Accessors
    public function getTotalStockAttribute(): int
    {
        if ($this->product_type === 'simple') {
            return $this->stock_quantity;
        }
        return $this->variants->sum('stock_quantity');
    }

    public function getIsInStockAttribute(): bool
    {
        if ($this->product_type === 'simple') {
            return $this->stock_status === 'in_stock';
        }
        return $this->variants->contains(fn($v) => $v->is_in_stock);
    }

    public function getIsOnSaleAttribute(): bool
    {
        if (!$this->sale_price) {
            return false;
        }
        $now = Carbon::now();
        
        if ($this->sale_start_date && $now < $this->sale_start_date) {
            return false;
        }
        if ($this->sale_end_date && $now > $this->sale_end_date) {
            return false;
        }
        return true;
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->is_on_sale && $this->sale_price) {
            return (float) $this->sale_price;
        }
        return (float) ($this->base_price ?? 0);
    }

    public function getDisplayPriceAttribute(): string
    {
        if ($this->is_on_sale && $this->compare_price) {
            return '<del>' . $this->formatPrice($this->compare_price) . '</del> ' . $this->formatPrice($this->final_price);
        }
        return $this->formatPrice($this->final_price);
    }

    public function getIsLowStockAttribute(): bool
    {
        if ($this->product_type === 'simple') {
            return $this->manage_stock && 
                   $this->stock_quantity > 0 && 
                   $this->stock_quantity <= $this->low_stock_threshold;
        }
        return $this->variants->contains(fn($v) => $v->is_low_stock);
    }

    // Helper Methods
    public function formatPrice($price): string
    {
        return '$' . number_format($price, 2);
    }

    public function updateStockStatus(): void
    {
        if ($this->product_type === 'simple' && $this->manage_stock) {
            $this->stock_status = $this->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
            $this->saveQuietly();
        }
    }

    public function hasVariantWithAttributes(array $attributeValueIds): ?Variant
    {
        foreach ($this->variants as $variant) {
            $variantValueIds = $variant->attributeValues->pluck('id')->sort()->values()->toArray();
            $searchIds = collect($attributeValueIds)->sort()->values()->toArray();
            
            if ($variantValueIds === $searchIds) {
                return $variant;
            }
        }
        return null;
    }
}
