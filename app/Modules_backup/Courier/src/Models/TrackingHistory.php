<?php

declare(strict_types=1);

namespace App\Modules\Courier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingHistory extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'location',
        'description',
        'raw_data',
        'tracked_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'tracked_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
