<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'pos_order_status_history';

    protected $fillable = [
        'pos_order_id',
        'status',
        'previous_status',
        'notes',
        'changed_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
