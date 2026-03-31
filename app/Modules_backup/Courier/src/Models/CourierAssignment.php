<?php

declare(strict_types=1);

namespace App\Modules\Courier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierAssignment extends Model
{
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
}
