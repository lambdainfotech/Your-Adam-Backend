<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_code',
        'coupon_discount',
    ];

    protected $casts = [
        'coupon_discount' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn (CartItem $item) => $item->quantity * $item->unit_price);
    }

    public function getTotalAttribute(): float
    {
        $subtotal = $this->subtotal;
        $discount = $this->coupon_discount ?? 0;

        return max(0, $subtotal - $discount);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
