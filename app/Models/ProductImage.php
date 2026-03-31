<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'image_url',
        'thumbnail_url',
        'alt_text',
        'sort_order',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    // Accessors
    public function getFullImageUrlAttribute(): string
    {
        return asset($this->image_url);
    }

    public function getFullThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_url ? asset($this->thumbnail_url) : null;
    }

    public function getIsVariantImageAttribute(): bool
    {
        return $this->variant_id !== null;
    }
}
