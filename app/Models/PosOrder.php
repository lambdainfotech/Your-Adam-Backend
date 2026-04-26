<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        // 'pos_session_id', // removed with POS session
        'user_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'is_wholesale',
        'delivery_status',
        'tracking_number',
        'delivery_address',
        'delivery_notes',
        'estimated_delivery_date',
        'delivered_at',
        'courier_id',
        'note',
    ];

    protected $casts = [
        'is_wholesale' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'POS-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    // Session relationship removed with POS session

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PosOrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function getPaymentMethodSummaryAttribute(): array
    {
        $summary = [
            'cash' => 0,
            'card' => 0,
            'bkash' => 0,
            'nagad' => 0,
            'other' => 0,
        ];

        foreach ($this->payments as $payment) {
            $summary[$payment->payment_method] += $payment->amount;
        }

        return $summary;
    }

    public function getDeliveryStatusBadgeClassAttribute(): string
    {
        return match($this->delivery_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'ready' => 'bg-purple-100 text-purple-800',
            'shipped' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function updateDeliveryStatus(string $newStatus, ?string $notes = null, ?int $changedBy = null): PosOrderStatusHistory
    {
        $previousStatus = $this->delivery_status;
        
        $this->update([
            'delivery_status' => $newStatus,
            'delivered_at' => $newStatus === 'delivered' ? now() : $this->delivered_at,
        ]);

        return $this->statusHistory()->create([
            'status' => $newStatus,
            'previous_status' => $previousStatus,
            'notes' => $notes,
            'changed_by' => $changedBy ?? auth()->id(),
        ]);
    }
}
