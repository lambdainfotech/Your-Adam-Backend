<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_id',
        'customer_type',
        'order_number',
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
        'admin_notes',
        'delivery_address',
        'billing_address',
        'estimated_delivery_date',
        'delivered_at',
        'transaction_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_address' => 'array',
        'billing_address' => 'array',
        'estimated_delivery_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the customer (user or guest) associated with this order.
     */
    public function customer(): User|Guest|null
    {
        return $this->customer_type === 'guest' ? $this->guest : $this->user;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function courierAssignment(): HasOne
    {
        return $this->hasOne(CourierAssignment::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereDate('created_at', '>=', $from)
                     ->whereDate('created_at', '<=', $to);
    }

    /**
     * Calculate shipping amount from shipping_zone for legacy orders
     * where shipping_amount was not stored.
     */
    public function getShippingAmountAttribute($value): float
    {
        $stored = (float) $value;
        if ($stored > 0 || empty($this->shipping_zone)) {
            return $stored;
        }

        $settings = Setting::allSettings();
        $subtotal = (float) $this->getAttributes()['subtotal'] ?? 0;
        $freeThreshold = (float) ($settings['free_shipping_threshold'] ?? 1000);

        if ($subtotal >= $freeThreshold) {
            return 0;
        }

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

        // If shipping was properly stored, return stored total
        if ($storedShipping > 0 || empty($this->shipping_zone)) {
            return $storedTotal;
        }

        // Recalculate: subtotal + tax + shipping - discount - coupon
        $subtotal = (float) ($this->getAttributes()['subtotal'] ?? 0);
        $tax = (float) ($this->getAttributes()['tax_amount'] ?? 0);
        $shipping = $this->shipping_amount; // Uses accessor above
        $discount = (float) ($this->getAttributes()['discount_amount'] ?? 0);
        $coupon = (float) ($this->getAttributes()['coupon_discount'] ?? 0);

        return max(0, $subtotal + $tax + $shipping - $discount - $coupon);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function addStatusHistory(string $newStatus, ?string $notes = null): OrderStatusHistory
    {
        return $this->statusHistory()->create([
            'status' => $newStatus,
            'previous_status' => $this->getOriginal('status') ?? $this->status,
            'notes' => $notes,
            'changed_by' => auth()->id(),
        ]);
    }
}
