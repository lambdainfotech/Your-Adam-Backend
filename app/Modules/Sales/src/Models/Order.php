<?php

declare(strict_types=1);

namespace App\Modules\Sales\Models;

use App\Models\Setting;
use App\Modules\Core\Models\User;
use App\Modules\Sales\Enums\OrderStatus;
use App\Modules\Sales\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_id',
        'customer_type',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'coupon_discount',
        'tax_amount',
        'shipping_amount',
        'shipping_zone',
        'total_amount',
        'currency',
        'notes',
        'delivery_address',
        'billing_address',
        'estimated_delivery_date',
        'transaction_id',
        'admin_notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'delivery_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'coupon_discount' => 'float',
        'tax_amount' => 'float',
        'shipping_amount' => 'float',
        'total_amount' => 'float',
        'estimated_delivery_date' => 'datetime',
    ];

    /**
     * Calculate shipping amount from shipping_zone for legacy orders
     * where shipping_amount was not stored properly.
     */
    public function getShippingAmountAttribute($value): float
    {
        $stored = (float) $value;
        if ($stored > 0 || empty($this->shipping_zone)) {
            return $stored;
        }

        $settings = Setting::allSettings();

        return match ($this->shipping_zone) {
            'inside_dhaka' => (float) ($settings['shipping_cost_inside_dhaka'] ?? $settings['default_shipping_cost'] ?? 60),
            'outside_dhaka' => (float) ($settings['shipping_cost_outside_dhaka'] ?? $settings['default_shipping_cost'] ?? 120),
            default => (float) ($settings['shipping_base_rate'] ?? 100),
        };
    }

    /**
     * Recalculate total_amount if shipping was missing from stored value.
     */
    public function getTotalAmountAttribute($value): float
    {
        $storedTotal = (float) $value;
        $storedShipping = (float) ($this->getAttributes()['shipping_amount'] ?? 0);

        if ($storedShipping > 0 || empty($this->shipping_zone)) {
            return $storedTotal;
        }

        $subtotal = (float) ($this->getAttributes()['subtotal'] ?? 0);
        $tax = (float) ($this->getAttributes()['tax_amount'] ?? 0);
        $shipping = $this->shipping_amount;
        $discount = (float) ($this->getAttributes()['discount_amount'] ?? 0);
        $coupon = (float) ($this->getAttributes()['coupon_discount'] ?? 0);

        return max(0, $subtotal + $tax + $shipping - $discount - $coupon);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::PENDING);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
