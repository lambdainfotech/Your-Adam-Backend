<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'phone',
        'email',
        'website',
        'tracking_url_template',
        'api_config',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getTrackingUrlAttribute(?string $trackingNumber): ?string
    {
        if (!$trackingNumber || !$this->tracking_url_template) {
            return null;
        }

        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_template);
    }
}
