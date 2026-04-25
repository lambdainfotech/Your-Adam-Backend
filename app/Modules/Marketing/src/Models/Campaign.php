<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Models;

use App\Modules\Marketing\Enums\CampaignStatus;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Campaign extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $table = 'campaigns';

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
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'apply_to_all' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function getCurrentStatus(): CampaignStatus
    {
        if (!$this->is_active) {
            return CampaignStatus::DISABLED;
        }

        $now = now();

        if ($this->starts_at > $now) {
            return CampaignStatus::SCHEDULED;
        }

        if ($this->ends_at < $now) {
            return CampaignStatus::EXPIRED;
        }

        return CampaignStatus::ACTIVE;
    }

    public function isActive(): bool
    {
        return $this->getCurrentStatus() === CampaignStatus::ACTIVE;
    }
}
