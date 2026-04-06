<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'banner_image',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'apply_to_all',
        'apply_type',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'apply_to_all' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = ['banner_image_url'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products')
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_categories')
            ->withTimestamps();
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        if (!$this->banner_image) {
            return null;
        }
        return asset('storage/' . $this->banner_image);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '>', now());
    }

    public function scopeEnded(Builder $query): Builder
    {
        return $query->where('ends_at', '<', now());
    }

    public function getIsRunningAttribute(): bool
    {
        return $this->is_active 
            && $this->starts_at <= now() 
            && $this->ends_at >= now();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->ends_at < now();
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->min_purchase_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ($this->discount_value / 100);
        } else {
            $discount = $this->discount_value;
        }

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $subtotal);
    }
}
