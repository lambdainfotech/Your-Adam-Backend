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
        'tracking_url',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'shipping_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
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
        return $this->hasMany(TrackingHistory::class, 'order_id', 'order_id');
    }

    public function getTrackingUrlAttribute(): ?string
    {
        return $this->courier?->getTrackingUrlAttribute($this->tracking_number);
    }
}
