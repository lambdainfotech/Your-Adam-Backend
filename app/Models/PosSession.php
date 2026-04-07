<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_amount',
        'closing_amount',
        'cash_sales',
        'card_sales',
        'mobile_sales',
        'other_sales',
        'status',
        'opened_at',
        'closed_at',
        'opening_note',
        'closing_note',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'mobile_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->cash_sales + $this->card_sales + $this->mobile_sales + $this->other_sales;
    }

    public function getExpectedCashAttribute(): float
    {
        return $this->opening_amount + $this->cash_sales;
    }

    public function getCashDifferenceAttribute(): float
    {
        if ($this->status === 'closed' && $this->closing_amount !== null) {
            return $this->closing_amount - $this->expected_cash;
        }
        return 0;
    }
}
