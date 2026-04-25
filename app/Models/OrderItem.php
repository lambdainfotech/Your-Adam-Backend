<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Get top selling products query builder.
     */
    public static function topSelling(int $limit = 10, ?string $startDate = null, ?string $endDate = null)
    {
        $query = DB::table('order_items')
            ->select(
                'products.id',
                'products.name',
                'products.sku_prefix',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            )
            ->join('variants', 'order_items.variant_id', '=', 'variants.id')
            ->join('products', 'variants.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.sku_prefix')
            ->orderBy('total_sold', 'desc')
            ->limit($limit);

        if ($startDate) {
            $query->whereDate('orders.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('orders.created_at', '<=', $endDate);
        }

        return $query;
    }

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
        'unit_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
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
