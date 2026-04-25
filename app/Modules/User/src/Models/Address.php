<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Modules\User\Enums\AddressType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'full_name',
        'mobile',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'landmark',
        'is_default',
    ];

    protected $casts = [
        'type' => AddressType::class,
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
