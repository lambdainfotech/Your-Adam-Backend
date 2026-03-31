<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourierAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'tracking_number',
        'shipping_cost',
        'estimated_delivery_date',
        'shipped_at',
        'delivered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function trackingHistory(): HasMany
    {
        return $this->hasMany(TrackingHistory::class);
    }

    public function getTrackingUrlAttribute(): ?string
    {
        return $this->courier?->getTrackingUrlAttribute($this->tracking_number);
    }
}
