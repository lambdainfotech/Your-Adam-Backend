<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use App\Modules\Catalog\Models\Variant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'variant_id',
        'product_name',
        'variant_sku',
        'variant_attributes',
        'quantity',
        'unit_price',
        'original_price',
        'discount_amount',
        'total_price',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'float',
        'original_price' => 'float',
        'discount_amount' => 'float',
        'total_price' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }
}
