<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'low_stock_alert',
        'weight',
        'is_active',
        'position',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_alert;
    }

    public function updateStockStatus(): void
    {
        if ($this->stock_quantity > 0) {
            $this->stock_status = 'in_stock';
        } else {
            $this->stock_status = 'out_of_stock';
        }
        $this->saveQuietly();
    }
}
