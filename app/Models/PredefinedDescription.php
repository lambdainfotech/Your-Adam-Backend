<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PredefinedDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'content',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDescriptions($query)
    {
        return $query->where('type', 'description');
    }

    public function scopeShortDescriptions($query)
    {
        return $query->where('type', 'short_description');
    }

    // Relationships
    public function productsUsingAsDescription(): HasMany
    {
        return $this->hasMany(Product::class, 'predefined_description_id');
    }

    public function productsUsingAsShortDescription(): HasMany
    {
        return $this->hasMany(Product::class, 'predefined_short_description_id');
    }

    // Helper Methods
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'description' ? 'Description' : 'Short Description';
    }

    public function getContentPreviewAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 100);
    }

    public function getProductsCountAttribute(): int
    {
        if ($this->type === 'description') {
            return $this->productsUsingAsDescription()->count();
        }
        return $this->productsUsingAsShortDescription()->count();
    }
}
