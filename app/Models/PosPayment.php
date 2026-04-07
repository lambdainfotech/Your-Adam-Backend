<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_order_id',
        'payment_method',
        'amount',
        'reference_number',
        'received_amount',
        'change_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function getIsCashAttribute(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function getDisplayPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'other' => 'Other',
            default => ucfirst($this->payment_method),
        };
    }
}
